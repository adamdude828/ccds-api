<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Video;
use App\Models\VideoStatus;
use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VideoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test video relationships.
     */
    public function test_video_has_relationships()
    {
        $grade = Grade::factory()->create();
        $subject = Subject::factory()->create();
        $uploader = User::factory()->create();
        $status = VideoStatus::factory()->create();
        
        $video = Video::factory()->create([
            'grade_id' => $grade->id,
            'subject_id' => $subject->id,
            'uploader_user_id' => $uploader->id,
            'video_status_id' => $status->id
        ]);
        
        $this->assertInstanceOf(Grade::class, $video->grade);
        $this->assertInstanceOf(Subject::class, $video->subject);
        $this->assertInstanceOf(User::class, $video->uploader);
        $this->assertInstanceOf(VideoStatus::class, $video->video_status);
    }

    /**
     * Test meta attribute is cast to array.
     */
    public function test_meta_is_cast_to_array()
    {
        $video = Video::factory()->create();
        
        $this->assertIsArray($video->meta);
        $this->assertArrayHasKey('input', $video->meta);
        $this->assertArrayHasKey('output', $video->meta);
    }

    /**
     * Test init meta method.
     */
    public function test_init_meta_creates_proper_structure()
    {
        $video = Video::factory()->create();
        $video->meta = null;
        $video->save();
        
        $video->initMeta();
        
        $this->assertNotNull($video->meta);
        $this->assertArrayHasKey('input', $video->meta);
        $this->assertArrayHasKey('output', $video->meta);
        $this->assertArrayHasKey('streaming-locator', $video->meta);
        $this->assertArrayHasKey('guid', $video->meta['input']);
        $this->assertArrayHasKey('container', $video->meta['input']);
        $this->assertArrayHasKey('asset', $video->meta['input']);
        $this->assertArrayHasKey('job', $video->meta['input']);
        $this->assertArrayHasKey('file', $video->meta['input']);
    }

    /**
     * Test getter methods for meta data.
     */
    public function test_meta_getter_methods()
    {
        $video = Video::factory()->create();
        
        $this->assertEquals($video->meta['input']['file'], $video->getInputFile());
        $this->assertEquals($video->meta['input']['guid'], $video->getInputGuid());
        $this->assertEquals($video->meta['input']['container'], $video->getInputContainer());
        $this->assertEquals($video->meta['output']['container'], $video->getOutputContainerName());
        $this->assertEquals($video->meta['input']['asset'], $video->getInputAssetName());
        $this->assertEquals($video->meta['output']['asset'], $video->getOutputAssetName());
        $this->assertEquals($video->meta['output']['poster-container'], $video->getOutputPosterContainer());
        $this->assertEquals($video->meta['output']['poster-asset'], $video->getOutputPosterAsset());
        $this->assertEquals($video->meta['output']['poster-image'], $video->getOutputPosterImage());
        $this->assertEquals($video->meta['input']['job'], $video->getJobName());
        $this->assertEquals($video->meta['streaming-locator'], $video->getStreamingLocatorName());
    }

    /**
     * Test public URL attribute.
     */
    public function test_public_url_attribute()
    {
        $video = Video::factory()->create([
            'streaming_url' => '/path/to/video.m3u8'
        ]);
        
        $expectedUrl = rtrim(config('azure.media.url'), '/') . '/path/to/video.m3u8';
        $this->assertEquals($expectedUrl, $video->public_url);
    }

    /**
     * Test public URL when streaming URL is empty.
     */
    public function test_public_url_when_streaming_url_is_empty()
    {
        $video = Video::factory()->create([
            'streaming_url' => null
        ]);
        
        $this->assertEquals('', $video->public_url);
    }

    /**
     * Test scope of type.
     */
    public function test_scope_of_type()
    {
        // Use existing VideoStatus records created by migrations
        $readyStatus = VideoStatus::where('name', VideoStatus::VIDEO_STATUS['VIDEO_READY'])->first();
        $draftStatus = VideoStatus::where('name', VideoStatus::VIDEO_STATUS['VIDEO_DRAFT'])->first();
        
        $readyVideo = Video::factory()->create(['video_status_id' => $readyStatus->id]);
        $draftVideo = Video::factory()->create(['video_status_id' => $draftStatus->id]);
        
        $readyVideos = Video::ofType(VideoStatus::VIDEO_STATUS['VIDEO_READY'])->get();
        $draftVideos = Video::ofType(VideoStatus::VIDEO_STATUS['VIDEO_DRAFT'])->get();
        
        $this->assertTrue($readyVideos->contains($readyVideo));
        $this->assertFalse($readyVideos->contains($draftVideo));
        $this->assertTrue($draftVideos->contains($draftVideo));
        $this->assertFalse($draftVideos->contains($readyVideo));
    }
} 