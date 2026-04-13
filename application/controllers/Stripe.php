<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Stripe Payment Controller
 * Handles Stripe credit card payments for subscription renewals.
 *
 * SECURITY:
 *  - Requires login session
 *  - Price always fetched from DB — never trusted from POST
 *  - API Secret Key loaded from config/stripe.php only
 *  - try/catch around all Stripe API calls
 */
class Stripe extends MY_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->load->model('subscription_model');
    }

    public function index() {
        // Redirect GET requests to expired page
        redirect('subscription/expired');
    }

    /**
     * Process Stripe payment (POST only, requires login).
     * Called from subscription_payment.php form.
     */
    public function payment() {
        // ── Auth Guard ───────────────────────────────────────────────
        if (!$this->session->userdata('logged_in')) {
            redirect('login'); return;
        }

        // ── POST Method Guard ────────────────────────────────────────
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            redirect('subscription/expired'); return;
        }

        // ── Validate required POST fields ────────────────────────────
        $stripeToken = $this->input->post('stripeToken');
        $package_id  = (int) $this->input->post('package_id');
        $plan_type   = $this->input->post('plan_type'); // 'Monthly' or 'Annually'
        $store_id    = (int) $this->session->userdata('store_id');

        if (empty($stripeToken) || empty($package_id) || empty($store_id)) {
            $this->session->set_flashdata('failed', 'ข้อมูลการชำระเงินไม่ครบถ้วน!');
            redirect('subscription/expired'); return;
        }

        // ── Fetch price from DB — NEVER trust POST amount ────────────
        $pkg = $this->db->where('id', $package_id)->get('bill_xml.db_package')->row();
        if (empty($pkg)) {
            $this->session->set_flashdata('failed', 'ไม่พบแพ็กเกจที่เลือก!');
            redirect('subscription/expired'); return;
        }

        // Calculate amount in smallest currency unit (satang/cents)
        $unit_price = ($plan_type === 'Annually')
            ? ($pkg->annual_price > 0 ? $pkg->annual_price : ($pkg->monthly_price * 10))
            : $pkg->monthly_price;

        if ($unit_price <= 0) {
            $this->session->set_flashdata('failed', 'แพ็กเกจนี้ไม่ต้องชำระเงิน!');
            redirect('subscription/status'); return;
        }

        // Convert to smallest unit (THB → สตางค์ × 100)
        $this->config->load('stripe');
        $currency     = $this->config->item('stripe_currency') ?: 'thb';
        $stripe_secret = $this->config->item('stripe_api_key');

        if (empty($stripe_secret) || strpos($stripe_secret, 'placeholder') !== false) {
            $this->session->set_flashdata('failed', 'ยังไม่ได้ตั้งค่า Stripe API Key กรุณาติดต่อผู้ดูแลระบบ!');
            redirect('subscription/renew_plan/' . $package_id); return;
        }

        $amount_in_cents = (int) round($unit_price * 100);

        // ── Charge via Stripe API ─────────────────────────────────────
        require_once(APPPATH . 'libraries/stripe/init.php');
        \Stripe\Stripe::setApiKey($stripe_secret);

        try {
            $charge = \Stripe\Charge::create([
                'amount'      => $amount_in_cents,
                'currency'    => $currency,
                'source'      => $stripeToken,
                'description' => 'Bill SaaS – ' . $pkg->package_name . ' [' . $plan_type . '] Store#' . $store_id,
                'metadata'    => [
                    'store_id'   => $store_id,
                    'package_id' => $package_id,
                    'plan_type'  => $plan_type,
                ],
            ]);

            if ($charge->status === 'succeeded') {
                // ── Record payment and activate subscription ──────────
                $this->load->model('super_admin_payment_model');
                $payment_data = [
                    'payment_method' => 'stripe',
                    'package_id'     => $package_id,
                    'plan_type'      => $plan_type,
                    'stripe_charge_id' => $charge->id,
                    'amount_paid'    => $unit_price,
                ];
                $result = $this->super_admin_payment_model->process_payment($payment_data);
                if (!is_array($result) || !isset($result['status']) || $result['status'] !== 'success') {
                    $this->session->set_flashdata('failed',
                        'ชำระเงินผ่านบัตรสำเร็จแล้ว แต่ระบบต่ออายุแพ็กเกจไม่สำเร็จ กรุณาติดต่อผู้ดูแลระบบพร้อม Charge ID: ' . $charge->id
                    );
                    log_message('error', 'Stripe charge succeeded but process_payment failed. Charge=' . $charge->id . ' result=' . json_encode($result));
                    redirect('subscription/renew_plan/' . $package_id);
                    return;
                }

                $this->session->set_flashdata('success',
                    'ชำระเงินผ่าน Stripe สำเร็จ! ต่ออายุแพ็กเกจ ' . $pkg->package_name . ' เรียบร้อยแล้ว (Charge ID: ' . $charge->id . ')'
                );
                $this->session->unset_userdata('is_expired');
                redirect('dashboard');

            } else {
                $this->session->set_flashdata('failed', 'การชำระเงินไม่สำเร็จ: ' . $charge->failure_message);
                redirect('subscription/renew_plan/' . $package_id);
            }

        } catch (\Stripe\Exception\CardException $e) {
            // Card was declined
            $this->session->set_flashdata('failed', 'บัตรถูกปฏิเสธ: ' . $e->getError()->message);
            redirect('subscription/renew_plan/' . $package_id);

        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $this->session->set_flashdata('failed', 'ข้อมูล Stripe ไม่ถูกต้อง: ' . $e->getMessage());
            redirect('subscription/renew_plan/' . $package_id);

        } catch (\Stripe\Exception\AuthenticationException $e) {
            $this->session->set_flashdata('failed', 'Stripe API Key ไม่ถูกต้อง กรุณาติดต่อผู้ดูแลระบบ!');
            redirect('subscription/expired');

        } catch (\Stripe\Exception\ApiConnectionException $e) {
            $this->session->set_flashdata('failed', 'ไม่สามารถเชื่อมต่อกับ Stripe ได้ กรุณาลองใหม่ภายหลัง');
            redirect('subscription/renew_plan/' . $package_id);

        } catch (\Exception $e) {
            log_message('error', 'Stripe payment error: ' . $e->getMessage());
            $this->session->set_flashdata('failed', 'เกิดข้อผิดพลาดในการชำระเงิน กรุณาติดต่อผู้ดูแลระบบ');
            redirect('subscription/renew_plan/' . $package_id);
        }
    }
}
