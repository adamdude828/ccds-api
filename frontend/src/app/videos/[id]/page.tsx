'use client';

import React, { useState, useEffect } from 'react';
import { useRouter, useParams } from 'next/navigation';
import { Button, Input, Select } from '@challenger-school/do-git-mis-components-storybook';
import { VideoService } from '@/services/videoService';
import { Video, Grade, Subject, VideoMode, VideoStatus } from '@/types/video';
import VideoPlayer from '@/components/videos/VideoPlayer';
import Textarea from '@/components/common/Textarea';

export default function EditVideoPage() {
  const router = useRouter();
  const params = useParams();
  const videoId = params.id as string;

  const [video, setVideo] = useState<Video | null>(null);
  const [title, setTitle] = useState('');
  const [day, setDay] = useState('1');
  const [subjectId, setSubjectId] = useState('');
  const [gradeId, setGradeId] = useState('');
  const [videoModeId, setVideoModeId] = useState('');
  const [description, setDescription] = useState('');
  const [grades, setGrades] = useState<Grade[]>([]);
  const [subjects, setSubjects] = useState<Subject[]>([]);
  const [videoModes, setVideoModes] = useState<VideoMode[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    loadData();
  }, [videoId]);

  const loadData = async () => {
    try {
      setLoading(true);
      const [videoData, gradesData, subjectsData, modesData] = await Promise.all([
        VideoService.getVideo(parseInt(videoId)),
        VideoService.getGrades(),
        VideoService.getSubjects(),
        VideoService.getVideoModes(),
      ]);

      setVideo(videoData);
      setTitle(videoData.title || '');
      setDay(videoData.day.toString());
      setSubjectId(videoData.subject_id.toString());
      setGradeId(videoData.grade_id.toString());
      setVideoModeId(videoData.video_mode_id.toString());
      setDescription(videoData.description || '');
      
      setGrades(gradesData);
      setSubjects(subjectsData);
      setVideoModes(modesData);
    } catch (error) {
      console.error('Failed to load video:', error);
      setError('Failed to load video data');
    } finally {
      setLoading(false);
    }
  };

  const handleSave = async () => {
    if (!video) return;

    setSaving(true);
    setError(null);

    try {
      await VideoService.updateVideo(video.id, {
        title,
        day: parseInt(day),
        subject_id: parseInt(subjectId),
        grade_id: parseInt(gradeId),
        video_mode_id: parseInt(videoModeId),
        description,
      });

      router.push('/videos');
    } catch (error) {
      console.error('Failed to update video:', error);
      setError('Failed to update video. Please try again.');
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="p-6">
        <p>Loading...</p>
      </div>
    );
  }

  if (!video) {
    return (
      <div className="p-6">
        <p>Video not found</p>
      </div>
    );
  }

  const gradeOptions = grades.map(grade => ({
    value: grade.id.toString(),
    label: grade.display_name,
  }));

  const subjectOptions = subjects.map(subject => ({
    value: subject.id.toString(),
    label: subject.display_name,
  }));

  const modeOptions = videoModes.map(mode => ({
    value: mode.id.toString(),
    label: mode.display_name,
  }));

  const canEditVideo = video.video_status.name === VideoStatus.VIDEO_READY || 
                       video.video_status.name === VideoStatus.DRAFT;

  return (
    <div className="p-6 max-w-4xl mx-auto">
      <h1 className="text-3xl font-bold mb-6">Edit Video</h1>

      {error && (
        <div className="mb-4 p-4 bg-red-100 text-red-700 rounded">
          {error}
        </div>
      )}

      <div className="space-y-6">
        {canEditVideo && (
          <div>
            <label className="block text-sm font-medium mb-2">Preview</label>
            <VideoPlayer
              src={video.private_url || video.public_url}
              poster={video.authenticated_poster}
              status={video.video_status.name}
              className="max-w-2xl"
            />
          </div>
        )}

        <div>
          <label className="block text-sm font-medium mb-2">
            Title <span className="text-red-500">*</span>
          </label>
          <Input
            value={title}
            onChange={(e) => setTitle(e.target.value)}
            placeholder="Video title"
            disabled={saving}
          />
        </div>

        <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label className="block text-sm font-medium mb-2">
              Day <span className="text-red-500">*</span>
            </label>
            <Input
              value={day}
              onChange={(e) => setDay(e.target.value)}
              type="number"
              min="1"
              placeholder="1"
              disabled={saving}
            />
          </div>

          <div>
            <label className="block text-sm font-medium mb-2">
              Subject <span className="text-red-500">*</span>
            </label>
            <Select
              options={subjectOptions}
              value={subjectOptions.find(opt => opt.value === subjectId)}
              onChange={(option) => setSubjectId(option.value)}
              disabled={saving}
            />
          </div>

          <div>
            <label className="block text-sm font-medium mb-2">
              Grade <span className="text-red-500">*</span>
            </label>
            <Select
              options={gradeOptions}
              value={gradeOptions.find(opt => opt.value === gradeId)}
              onChange={(option) => setGradeId(option.value)}
              disabled={saving}
            />
          </div>

          <div>
            <label className="block text-sm font-medium mb-2">
              Mode <span className="text-red-500">*</span>
            </label>
            <Select
              options={modeOptions}
              value={modeOptions.find(opt => opt.value === videoModeId)}
              onChange={(option) => setVideoModeId(option.value)}
              disabled={saving}
            />
          </div>
        </div>

        <div>
          <label className="block text-sm font-medium mb-2">
            Description
          </label>
          <Textarea
            value={description}
            onChange={setDescription}
            placeholder="Enter video description..."
            rows={4}
            disabled={saving}
          />
        </div>

        <div className="bg-gray-50 p-4 rounded">
          <h3 className="font-medium mb-2">Video Information</h3>
          <div className="grid grid-cols-2 gap-2 text-sm">
            <div>
              <span className="text-gray-600">Status:</span>{' '}
              <span className="font-medium">{video.video_status.display_name}</span>
            </div>
            <div>
              <span className="text-gray-600">Uploaded by:</span>{' '}
              <span className="font-medium">{video.uploader?.name || 'Unknown'}</span>
            </div>
            <div>
              <span className="text-gray-600">Created:</span>{' '}
              <span className="font-medium">{new Date(video.created_at).toLocaleString()}</span>
            </div>
            <div>
              <span className="text-gray-600">Updated:</span>{' '}
              <span className="font-medium">{new Date(video.updated_at).toLocaleString()}</span>
            </div>
          </div>
        </div>

        <div className="flex justify-end space-x-3">
          <Button
            label="Cancel"
            variant="secondary"
            onClick={() => router.push('/videos')}
            disabled={saving}
          />
          <Button
            label="Save Changes"
            variant="primary"
            onClick={handleSave}
            disabled={saving || !title || !subjectId || !gradeId || !videoModeId}
          />
        </div>
      </div>
    </div>
  );
}