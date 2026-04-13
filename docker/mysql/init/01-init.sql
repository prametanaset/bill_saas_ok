-- Grant the app user full access to bill_xml and any tenant databases (bill_xml_st*)
GRANT ALL PRIVILEGES ON `bill_xml`.* TO 'bill_user'@'%';
GRANT ALL PRIVILEGES ON `bill_xml\_%`.* TO 'bill_user'@'%';
FLUSH PRIVILEGES;
