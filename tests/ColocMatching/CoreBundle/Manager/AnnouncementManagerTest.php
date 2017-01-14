<?php
namespace Test\ColocMatching\CoreBundle\Manager;

use PHPUnit\Framework\TestCase;
use ColocMatching\CoreBundle\Manager\Announcement\AnnouncementManager;
use ColocMatching\CoreBundle\Entity\Announcement\Announcement;
use ColocMatching\CoreBundle\Repository\Filter\AbstractFilter;
use ColocMatching\CoreBundle\Repository\Filter\AnnouncementFilter;
use ColocMatching\CoreBundle\Entity\User\User;

class AnnouncementManagerTest extends TestCase {

	private $announcementManager;
	
	
	public function setUp() {
		$this->announcementManager = $this->createMock(AnnouncementManager::class);
	}
	
	
	public function testGetAnnouncements() {
		/** @var array */
		$mockedAnnouncements = [];
		
		for ($i = 1; $i <= 10; $i++) {
			$mockedAnnouncement = $this->createMock(Announcement::class);
			$mockedAnnouncement->expects($this->any())->method("getId")->willReturn($i);
			
			$mockedAnnouncements[] = $mockedAnnouncement;
		}
		
		$this->announcementManager->expects($this->once())->method("getAll")->with($this->isInstanceOf(AbstractFilter::class))->willReturn($mockedAnnouncements);
		$this->announcementManager->expects($this->once())->method("countAll")->willReturn(count($mockedAnnouncements));
		
		$announcements = $this->announcementManager->getAll(new AnnouncementFilter());
		$nbAnnouncements = $this->announcementManager->countAll();
		
		$this->assertNotEmpty($announcements);
		$this->assertEquals(count($mockedAnnouncements), $nbAnnouncements);
		
		for ($i = 0; $i < count($announcements); $i++) {
			$this->assertEquals($announcements[$i], $mockedAnnouncements[$i]);
		}
	}
	
	
	public function testCreateAnnouncement() {
		$user = $this->createMock(User::class);
		$user->expects($this->any())->method("getId")->willReturn(1);
		
		$mockedAnnouncement = $this->createMock(Announcement::class);
		$mockedAnnouncement->expects($this->any())->method("getId")->willReturn(1);
		$mockedAnnouncement->expects($this->any())->method("getTitle")->willReturn("Announcement test");
		$mockedAnnouncement->expects($this->any())->method("getMinPrice")->willReturn(500);
		$mockedAnnouncement->expects($this->any())->method("getTitle")->willReturn("Announcement test");
	}
	
}