<?php

namespace Drupal\suffix_zx_commerce\Entity;

use Drupal\commerce_product\Entity\Product;

/**
 * Defines the product entity class.
 *
 * @ContentEntityType(
 *   id = "suffix_zx_commerce_product",
 *   label = @Translation("suff Product"),
 *   label_collection = @Translation("suff Products"),
 *   label_singular = @Translation("product"),
 *   label_plural = @Translation("products"),
 *   label_count = @PluralTranslation(
 *     singular = "@count product",
 *     plural = "@count products",
 *   ),
 *   bundle_label = @Translation("Product type"),
 *   handlers = {
 *     "event" = "Drupal\commerce_product\Event\ProductEvent",
 *     "storage" = "Drupal\commerce\CommerceContentEntityStorage",
 *     "access" = "Drupal\commerce\EntityAccessControlHandler",
 *     "permission_provider" = "Drupal\commerce\EntityPermissionProvider",
 *     "view_builder" = "Drupal\commerce_product\ProductViewBuilder",
 *     "list_builder" = "Drupal\commerce_product\ProductListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\commerce_product\Form\ProductForm",
 *       "add" = "Drupal\commerce_product\Form\ProductForm",
 *       "edit" = "Drupal\commerce_product\Form\ProductForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *       "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
 *     },
 *     "translation" = "Drupal\commerce_product\ProductTranslationHandler"
 *   },
 *   admin_permission = "administer commerce_product",
 *   permission_granularity = "bundle",
 *   fieldable = TRUE,
 *   translatable = TRUE,
 *   base_table = "commerce_product",
 *   data_table = "commerce_product_field_data",
 *   entity_keys = {
 *     "id" = "product_id",
 *     "bundle" = "type",
 *     "label" = "title",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "published" = "status",
 *   },
 *   links = {
 *     "canonical" = "/ddproduct/{commerce_product}",
 *     "add-page" = "/product/add",
 *     "add-form" = "/product/add/{commerce_product_type}",
 *     "edit-form" = "/product/{commerce_product}/edit",
 *     "delete-form" = "/product/{commerce_product}/delete",
 *     "delete-multiple-form" = "/admin/commerce/products/delete",
 *     "collection" = "/admin/commerce/products"
 *   },
 *   bundle_entity_type = "commerce_product_type",
 *   field_ui_base_route = "entity.commerce_product_type.edit_form",
 * )
 */
class Product extends Product {


}
