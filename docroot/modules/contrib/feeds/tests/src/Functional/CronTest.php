<?php

namespace Drupal\Tests\feeds\Functional;

/**
 * Tests behavior involving periodic import.
 *
 * @group feeds
 */
class CronTest extends FeedsBrowserTestBase {

  /**
   * Tests importing through cron.
   */
  public function test() {
    $feed_type = $this->createFeedType();

    // Set import period to once an hour and unset unique target.
    $feed_type->setImportPeriod(3600);
    $mappings = $feed_type->getMappings();
    unset($mappings[0]['unique']);
    $feed_type->setMappings($mappings);
    $feed_type->save();

    $feed = $this->createFeed($feed_type->id(), [
      'source' => $this->resourcesUrl() . '/rss/googlenewstz.rss2',
    ]);

    // Verify initial values.
    $feed = $this->reloadEntity($feed);
    $this->assertEquals(0, $feed->getImportedTime());
    $this->assertEquals(0, $feed->getNextImportTime());
    $this->assertEquals(0, $feed->getItemCount());

    // Cron should import some nodes.
    // Clear the download cache so that the http fetcher doesn't trick us.
    \Drupal::cache('feeds_download')->deleteAll();
    sleep(1);
    $this->cronRun();
    $feed = $this->reloadEntity($feed);

    $this->assertEquals(6, $feed->getItemCount());
    $imported = $feed->getImportedTime();
    $this->assertTrue($imported > 0);
    $this->assertEquals($imported + 3600, $feed->getNextImportTime());

    // Nothing should change on this cron run.
    \Drupal::cache('feeds_download')->deleteAll();
    sleep(1);
    $this->cronRun();
    $feed = $this->reloadEntity($feed);

    $this->assertEquals(6, $feed->getItemCount());
    $this->assertEquals($imported, $feed->getImportedTime());
    $this->assertEquals($imported + 3600, $feed->getNextImportTime());

    // Check that items import normally.
    \Drupal::cache('feeds_download')->deleteAll();
    sleep(1);
    $this->drupalPostForm('feed/' . $feed->id() . '/import', [], t('Import'));
    $feed = $this->reloadEntity($feed);

    $manual_imported_time = $feed->getImportedTime();
    $this->assertEquals(12, $feed->getItemCount());
    $this->assertTrue($manual_imported_time > $imported);
    $this->assertEquals($feed->getImportedTime() + 3600, $feed->getNextImportTime());

    // Change the next time so that the feed should be scheduled. Then, disable
    // it to ensure the status is respected.
    // Nothing should change on this cron run.
    $feed = $this->reloadEntity($feed);
    $feed->set('next', 0);
    $feed->setActive(FALSE);
    $feed->save();

    \Drupal::cache('feeds_download')->deleteAll();
    sleep(1);
    $this->cronRun();
    $feed = $this->reloadEntity($feed);

    $this->assertEquals(12, $feed->getItemCount());
    $this->assertEquals($manual_imported_time, $feed->getImportedTime());
    $this->assertEquals(0, $feed->getNextImportTime());
  }

}
