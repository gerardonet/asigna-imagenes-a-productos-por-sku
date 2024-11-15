<?php
/**
 * Plugin Name: Asignar Imágenes por SKU a Productos
 * Plugin URI: https://netcommerce.mx
 * Description: Asigna automáticamente las imágenes de la biblioteca de medios a los productos de WooCommerce según el SKU.
 * Version: 1.0.4
 * Author: Netcommerce
 * Author URI: https://netcommerce.mx
 * License: GPL2
 */

// Evita el acceso directo a este archivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Función para asignar imágenes a productos por SKU
function assign_images_to_products_by_sku($product_id) {
    // Verifica si el producto tiene SKU
    $product_sku = get_post_meta($product_id, '_sku', true);
    
    if ($product_sku) {
        // Argumentos para obtener las imágenes en la biblioteca de medios
        $image_args = array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_wp_attached_file',
                    'value' => $product_sku,
                    'compare' => 'LIKE'
                )
            )
        );

        // Obtener las imágenes que coincidan con el SKU
        $images = get_posts($image_args);

        if (!empty($images)) {
            $gallery_image_ids = array();

            foreach ($images as $index => $image) {
                // Asignar la primera imagen como imagen destacada
                if ($index == 0) {
                    set_post_thumbnail($product_id, $image->ID);
                } else {
                    // Añadir el resto de imágenes a la galería
                    $gallery_image_ids[] = $image->ID;
                }
            }

            // Actualizar la galería de imágenes del producto
            if (!empty($gallery_image_ids)) {
                // Asignar las imágenes a la galería
                update_post_meta($product_id, '_product_image_gallery', implode(',', $gallery_image_ids));
            }
        }
    }
}

// Ejecutar la función cuando un producto es guardado o actualizado
add_action('woocommerce_update_product', 'assign_images_to_products_by_sku');
add_action('woocommerce_product_import_inserted_product_object', 'assign_images_to_products_by_sku');

// Cargar el archivo para las actualizaciones automáticas
require 'plugin-update-checker.php'; // Asegúrate de que la ruta sea correcta

// Crear un objeto para verificar y gestionar las actualizaciones automáticas
$my_update_checker = \YahnisElsts\PluginUpdateChecker\v5p5\PucFactory::buildUpdateChecker(
    'https://github.com/gerardonet/asigna-imagenes-a-productos-por-sku',  // URL de tu repositorio en GitHub
    __FILE__,  // Ruta al archivo principal del plugin
    'asigna-imagenes-a-productos-por-sku'  // Slug único para tu plugin (puede ser el nombre del plugin en minúsculas)
);
