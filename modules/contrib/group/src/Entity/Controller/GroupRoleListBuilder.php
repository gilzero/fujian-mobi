<?php

namespace Drupal\group\Entity\Controller;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\group\Entity\GroupRoleInterface;
use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group\PermissionScopeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a class to build a listing of group role entities.
 *
 * @see \Drupal\group\Entity\GroupRole
 */
class GroupRoleListBuilder extends DraggableListBuilder {

  /**
   * The group type to check for roles.
   *
   * @var \Drupal\group\Entity\GroupTypeInterface
   */
  protected $groupType;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, RouteMatchInterface $route_match) {
    parent::__construct($entity_type, $storage);

    // When on the default group role list route, we should have a group type.
    if ($route_match->getRouteName() == 'entity.group_role.collection') {
      $parameters = $route_match->getParameters();

      // Check if the route had a group type parameter.
      if ($parameters->has('group_type') && $group_type = $parameters->get('group_type')) {
        if ($group_type instanceof GroupTypeInterface) {
          $this->groupType = $group_type;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()
      ->condition('group_type', $this->groupType->id(), '=')
      ->sort($this->entityType->getKey('weight'));

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }

    return array_values($query->accessCheck()->execute());
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'group_admin_roles';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Name');
    $header['scope'] = $this->t('Scope');
    $header['global_role'] = $this->t('Synchronizes with');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    assert($entity instanceof GroupRoleInterface);
    if ($entity->getScope() === PermissionScopeInterface::INDIVIDUAL_ID) {
      $scope_label = $this->t('Individual');
      $global_role = $this->t('n/a');
    }
    else {
      $scope_label = $entity->getScope() === PermissionScopeInterface::OUTSIDER_ID ? $this->t('Outsider') : $this->t('Insider');
      $global_role = $entity->getGlobalRole()->label();
    }

    $row['label'] = $entity->label();
    $row['scope'] = ['#plain_text' => $scope_label];
    $row['global_role'] = ['#plain_text' => $global_role];

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if ($entity->hasLinkTemplate('permissions-form')) {
      $operations['permissions'] = [
        'title' => $this->t('Edit permissions'),
        'weight' => 5,
        'url' => $entity->toUrl('permissions-form'),
      ];
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['info'] = [
      '#prefix' => '<p>' . $this->t('Group roles can belong to one of three scopes:') . '</p>',
      '#theme' => 'item_list',
      '#items' => [
        ['#markup' => $this->t('<strong>Outsider:</strong> Assigned to all non-members who have the corresponding global role.')],
        ['#markup' => $this->t('<strong>Insider:</strong> Assigned to all members who have the corresponding global role.')],
        ['#markup' => $this->t('<strong>Individual:</strong> Can be assigned to individual members.')],
      ],
    ];

    $build += parent::render();
    $build['table']['#empty'] = $this->t('No group roles available. <a href="@link">Add group role</a>.', [
      '@link' => Url::fromRoute('entity.group_role.add_form', ['group_type' => $this->groupType->id()])->toString(),
    ]);

    return $build;
  }

}
