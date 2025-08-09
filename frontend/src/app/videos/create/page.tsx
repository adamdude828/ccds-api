'use client';

import React, { useState, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { Button, Input, Select } from '@challenger-school/do-git-mis-components-storybook';
import { VideoService } from '@/services/videoService';
import { AzureStorageService, UploadProgress } from '@/services/azureStorageService';
import { Grade, Subject, VideoUploadCredentials } from '@/types/video';
import FileUpload from '@/components/videos/FileUpload';
import Textarea from '@/components/common/Textarea';
import Modal from '@/components/common/Modal';

export default function CreateVideoPage() {
  const router = useRouter();
  const [videoFile, setVideoFile] = useState<File | null>(null);
  const [posterFile, setPosterFile] = useState<File | null>(null);
  const [day, setDay] = useState('1');
  const [subjectId, setSubjectId] = useState<string>('');
  const [gradeId, setGradeId] = useState<string>('');
  const [description, setDescription] = useState('');
  const [grades, setGrades] = useState<Grade[]>([]);
  const [subjects, setSubjects] = useState<Subject[]>([]);
  const [loading, setLoading] = useState(false);
  const [uploadProgress, setUploadProgress] = useState<UploadProgress | null>(null);
  const [uploadModalOpen, setUploadModalOpen] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    loadFormData();
  }, []);

  const loadFormData = async () => {
    try {
      const [gradesData, subjectsData] = await Promise.all([
        VideoService.getGrades(),
        VideoService.getSubjects(),
      ]);
      setGrades(gradesData);
      setSubjects(subjectsData);
    } catch (error) {
      console.error('Failed to load form data:', error);
      setError('Failed to load form data');
    }
  };

  const handleSubmit = async () => {
    if (!videoFile || !subjectId || !gradeId) {
      setError('Please fill in all required fields');
      return;
    }

    setLoading(true);
    setUploadModalOpen(true);
    setError(null);

    try {
      // Create form data
      const formData = new FormData();
      formData.append('videos[]', videoFile);
      if (posterFile) {
        formData.append('posters[]', posterFile);
      }
      formData.append('subject_id', subjectId);
      formData.append('grade_id', gradeId);
      formData.append('day', day);
      if (description) {
        formData.append('description', description);
      }

      // Get upload credentials
      const credentials = await VideoService.createVideo(formData);
      
      // Upload video to Azure
      const videoCredentials = credentials[0];
      await AzureStorageService.uploadToBlob(
        videoFile,
        videoCredentials.upload_url,
        (progress) => setUploadProgress(progress)
      );

      // Mark upload as complete
      await VideoService.uploadComplete([videoCredentials.video_id]);

      // Navigate to videos list
      router.push('/videos');
    } catch (error) {
      console.error('Failed to create video:', error);
      setError('Failed to create video. Please try again.');
      setUploadModalOpen(false);
    } finally {
      setLoading(false);
    }
  };

  const gradeOptions = grades.map(grade => ({
    value: grade.id.toString(),
    label: grade.display_name,
  }));

  const subjectOptions = subjects.map(subject => ({
    value: subject.id.toString(),
    label: subject.display_name,
  }));

  return (
    <div className="p-6 max-w-4xl mx-auto">
      <h1 className="text-3xl font-bold mb-6">Add New Video</h1>

      {error && (
        <div className="mb-4 p-4 bg-red-100 text-red-700 rounded">
          {error}
        </div>
      )}

      <div className="space-y-6">
        <div>
          <label className="block text-sm font-medium mb-2">
            Video File <span className="text-red-500">*</span>
          </label>
          <FileUpload
            accept="video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv"
            onFileSelect={setVideoFile}
            label="Upload video file (MP4, MOV, AVI, WMV)"
            disabled={loading}
            maxSize={5 * 1024 * 1024 * 1024} // 5GB
          />
          {videoFile && (
            <p className="mt-2 text-sm text-gray-600">
              Selected: {videoFile.name} ({(videoFile.size / 1024 / 1024).toFixed(2)} MB)
            </p>
          )}
        </div>

        <div>
          <label className="block text-sm font-medium mb-2">
            Poster Image (Optional)
          </label>
          <FileUpload
            accept="image/jpeg,image/png,image/webp"
            onFileSelect={setPosterFile}
            label="Upload poster image (JPG, PNG, WebP)"
            disabled={loading}
            maxSize={10 * 1024 * 1024} // 10MB
          />
          {posterFile && (
            <p className="mt-2 text-sm text-gray-600">
              Selected: {posterFile.name}
            </p>
          )}
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
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
              disabled={loading}
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
              disabled={loading}
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
              disabled={loading}
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
            disabled={loading}
          />
        </div>

        <div className="flex justify-end space-x-3">
          <Button
            label="Cancel"
            variant="secondary"
            onClick={() => router.push('/videos')}
            disabled={loading}
          />
          <Button
            label="Save"
            variant="primary"
            onClick={handleSubmit}
            disabled={loading || !videoFile || !subjectId || !gradeId}
          />
        </div>
      </div>

      {/* Upload Progress Modal */}
      <Modal
        isOpen={uploadModalOpen}
        onClose={() => {}}
        title="Uploading Video"
        size="sm"
        closeOnBackdrop={false}
      >
        <div className="space-y-4">
          <p className="text-sm text-gray-600">
            Please wait while your video is being uploaded...
          </p>
          {uploadProgress && (
            <div>
              <div className="flex justify-between text-sm mb-1">
                <span>Progress</span>
                <span>{uploadProgress.percentage}%</span>
              </div>
              <div className="w-full bg-gray-200 rounded-full h-2">
                <div
                  className="bg-blue-600 h-2 rounded-full transition-all duration-300"
                  style={{ width: `${uploadProgress.percentage}%` }}
                />
              </div>
              <p className="text-xs text-gray-500 mt-1">
                {(uploadProgress.loaded / 1024 / 1024).toFixed(2)} MB of{' '}
                {(uploadProgress.total / 1024 / 1024).toFixed(2)} MB
              </p>
            </div>
          )}
        </div>
      </Modal>
    </div>
  );
}