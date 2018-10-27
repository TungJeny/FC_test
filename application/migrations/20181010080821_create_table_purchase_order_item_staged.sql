CREATE TABLE `phppos_purchase_order_item_staged` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `month_staged` varchar(11) DEFAULT NULL,
  `purchase_order_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

ALTER TABLE phppos_purchase_order_items ADD CONSTRAINT fk_purchase_order_item_staged FOREIGN KEY(item_id) REFERENCES  phppos_purchase_order_item_staged(item_id) ON DELETE CASCADE;