<?php
namespace Drupal\Tests\phpunit_example\Unit;
use Drupal\Tests\UnitTestCase;

/**
 * Simple test to ensure that asserts pass.
 *
 * @group phpunit_example
 */
class WebpTest extends UnitTestCase {
  protected $webp;
  /**
   * Before a test method is run, setUp() is invoked.
   * Create new unit object.
   */
  public function setUp() {
    // Mock the class to avoid the constructor.
    $this->webp = $this->getMockBuilder('\Drupal\webp\Webp')
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();
  }

  /**
   * @covers Drupal\webp\Webp::getWebpFilename
   */
  public function testgetWebpFilename() {
    $this->assertEquals("testimage.webp", $this->webp->getWebpFilename("testimage.jpg"));
    $this->assertEquals("testimage2.webp", $this->webp->getWebpFilename("testimage2.png"));
    $this->assertEquals("testimage2.webp", $this->webp->getWebpFilename("testimage2.jpeg"));
    $this->assertEquals("testimage2.webp", $this->webp->getWebpFilename("testimage2.jpg"));
    $this->assertEquals("testimage2.ext.webp", $this->webp->getWebpFilename("testimage2.ext.jpg"));
    $this->assertEquals("testimage2.ext.webp", $this->webp->getWebpFilename("testimage2.ext.jpg"));
    $this->assertEquals("testimage2.ext.ext.webp", $this->webp->getWebpFilename("testimage2.ext.ext.jpg"));
    $this->assertEquals("testimage2.ext.webp", $this->webp->getWebpFilename("testimage2.ext.jpg"));

    $this->assertEquals("url/sites/default/files/styles/large/public/2019-07/IMG_1949-orig_8.webp", $this->webp->getWebpFilename("url/sites/default/files/styles/large/public/2019-07/IMG_1949-orig_8.JPG"));
    $this->assertEquals("url/sites/default/files/styles/large/public/2019-07/IMG_1949-orig_7.webp?itok=vOpRgtYZ", $this->webp->getWebpFilename("url/sites/default/files/styles/large/public/2019-07/IMG_1949-orig_7.JPG?itok=vOpRgtYZ"));

  }

}
