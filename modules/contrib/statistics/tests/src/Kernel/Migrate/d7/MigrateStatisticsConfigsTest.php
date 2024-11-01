<?php

declare(strict_types=1);

namespace Drupal\Tests\statistics\Kernel\Migrate\d7;

use Drupal\Tests\migrate_drupal\Kernel\d7\MigrateDrupal7TestBase;
use Drupal\Tests\SchemaCheckTestTrait;

/**
 * Upgrade variables to statistics.settings.yml.
 *
 * @group migrate_drupal_7
 */
class MigrateStatisticsConfigsTest extends MigrateDrupal7TestBase {

  use SchemaCheckTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['statistics'];

  /**
   * Tests migration of statistics variables to statistics.settings.yml.
   */
  public function testStatisticsSettings() {
    $config = $this->config('statistics.settings');
    $this->assertSame(1, $config->get('count_content_views'));
    $this->assertConfigSchema(\Drupal::service('config.typed'), 'statistics.settings', $config->get());
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->executeMigration('statistics_settings');
  }

  /**
   * Gets the path to the fixture file.
   */
  protected function getFixtureFilePath() {
    return __DIR__ . '/../../../../fixtures/drupal7.php';
  }

}
