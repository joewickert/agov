<?php

/**
 * @file
 * AGov profile global changes.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;

/**
 * Implements hook_form_FORM_ID_alter().
 */
function agov_form_contact_message_contact_form_alter(&$form, FormStateInterface $form_state) {
  $form['actions']['preview']['#access'] = FALSE;
}

/**
 * Implements hook_form_FORM_ID_alter().
 */
function agov_form_user_login_form_alter(&$form, &$form_state) {
  $form['#attributes']['autocomplete'] = 'off';
}

/**
 * Implements hook_system_breadcrumb_alter().
 */
function agov_system_breadcrumb_alter(Breadcrumb $breadcrumb, RouteMatchInterface $route_match, array $context) {
  // Append the current page title to the breadcrumb for non-admin routes.
  if ($breadcrumb && !\Drupal::service('router.admin_context')->isAdminRoute()) {
    $title = \Drupal::service('title_resolver')->getTitle(\Drupal::request(), $route_match->getRouteObject());
    if (!empty($title)) {
      $breadcrumb->addLink(Link::createFromRoute($title, '<none>'));
    }
    $breadcrumb->addCacheContexts(['route']);
  }
}
