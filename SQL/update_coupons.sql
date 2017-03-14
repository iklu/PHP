INSERT INTO `dma_has_coupons`(`dma_id`, `coupons_id`) SELECT id, 2 FROM dma;
INSERT INTO `dma_has_coupons`(`dma_id`, `coupons_id`) SELECT id, 3 FROM dma;
INSERT INTO `dma_has_coupons`(`dma_id`, `coupons_id`) SELECT id, 4 FROM dma;
INSERT INTO `dma_has_coupons`(`dma_id`, `coupons_id`) SELECT id, 5 FROM dma;

SELECT DISTINCT state, dmaName
FROM dma
ORDER BY dmaName
LIMIT 0 , 30
