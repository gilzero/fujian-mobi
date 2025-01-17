<?php

namespace Drupal\statistics\Plugin\views\field;

use Drupal\Core\Session\AccountInterface;
use Drupal\views\Attribute\ViewsField;
use Drupal\views\Plugin\views\field\Date;

/**
 * Field handler to display the most recent time the node has been viewed.
 *
 * @ingroup views_field_handlers
 */
#[ViewsField("node_counter_timestamp")]
class NodeCounterTimestamp extends Date {

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) {
    return $account->hasPermission('view post access counter');
  }

}
