'use client';

import React, { useState, useEffect, useCallback, useMemo } from 'react';
import { useRouter } from 'next/navigation';
import { ItemGrid, Button, Input, Checkbox } from '@challenger-school/do-git-mis-components-storybook';
import { VideoService } from '@/services/videoService';
import { Video, VideoStatus } from '@/types/video';
import StatusBadge from '@/components/videos/StatusBadge';
import Pagination from '@/components/common/Pagination';
import Modal from '@/components/common/Modal';
import VideoPlayer from '@/components/videos/VideoPlayer';
import MassActionModal from '@/components/videos/MassActionModal';
import type { Column } from '@challenger-school/do-git-mis-components-storybook';

interface ExtendedVideo extends Video {
  selected?: boolean;
}

export default function VideosPage() {
  const router = useRouter();
  const [videos, setVideos] = useState<ExtendedVideo[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [selectedVideos, setSelectedVideos] = useState<Set<number>>(new Set());
  const [deleteModalOpen, setDeleteModalOpen] = useState(false);
  const [videoToDelete, setVideoToDelete] = useState<Video | null>(null);
  const [deleteConfirmation, setDeleteConfirmation] = useState('');
  const [previewModalOpen, setPreviewModalOpen] = useState(false);
  const [videoToPreview, setVideoToPreview] = useState<Video | null>(null);
  const [sortConfig, setSortConfig] = useState<{ field: string; direction: 'asc' | 'desc' } | null>(null);
  const [massActionModalOpen, setMassActionModalOpen] = useState(false);

  const loadVideos = useCallback(async () => {
    try {
      setLoading(true);
      const response = await VideoService.listVideos(currentPage, search, sortConfig || undefined);
      const videosWithSelection = response.data.map(video => ({
        ...video,
        selected: selectedVideos.has(video.id),
      }));
      setVideos(videosWithSelection);
      setTotalPages(response.last_page);
    } catch (error) {
      console.error('Failed to load videos:', error);
    } finally {
      setLoading(false);
    }
  }, [currentPage, search, sortConfig, selectedVideos]);

  useEffect(() => {
    loadVideos();
  }, [loadVideos]);

  const handleSearch = useCallback(() => {
    setCurrentPage(1);
    loadVideos();
  }, [loadVideos]);

  const handleSort = (field: string) => {
    setSortConfig(prev => {
      if (prev?.field === field) {
        return { field, direction: prev.direction === 'asc' ? 'desc' : 'asc' };
      }
      return { field, direction: 'asc' };
    });
  };

  const handleSelectAll = (checked: boolean) => {
    if (checked) {
      const newSelection = new Set(videos.map(v => v.id));
      setSelectedVideos(newSelection);
      setVideos(videos.map(v => ({ ...v, selected: true })));
    } else {
      setSelectedVideos(new Set());
      setVideos(videos.map(v => ({ ...v, selected: false })));
    }
  };

  const handleSelectVideo = (videoId: number, checked: boolean) => {
    const newSelection = new Set(selectedVideos);
    if (checked) {
      newSelection.add(videoId);
    } else {
      newSelection.delete(videoId);
    }
    setSelectedVideos(newSelection);
    setVideos(videos.map(v => ({
      ...v,
      selected: v.id === videoId ? checked : v.selected,
    })));
  };

  const handleDelete = async () => {
    if (!videoToDelete || deleteConfirmation !== videoToDelete.title) return;

    try {
      await VideoService.deleteVideo(videoToDelete.id);
      setDeleteModalOpen(false);
      setVideoToDelete(null);
      setDeleteConfirmation('');
      loadVideos();
    } catch (error) {
      console.error('Failed to delete video:', error);
    }
  };

  const isActionEnabled = (video: Video) => {
    return video.video_status.name === VideoStatus.VIDEO_READY || 
           video.video_status.name === VideoStatus.DRAFT;
  };

  const columns: Column<ExtendedVideo>[] = useMemo(() => [
    {
      key: 'checkbox' as keyof ExtendedVideo,
      label: '',
      render: (video: ExtendedVideo) => (
        <Checkbox
          label=""
          checked={video.selected || false}
          onChange={(checked) => handleSelectVideo(video.id, checked)}
        />
      ),
      width: '50px',
    },
    {
      key: 'id',
      label: 'ID',
      sortable: true,
    },
    {
      key: 'day',
      label: 'Day',
      sortable: true,
    },
    {
      key: 'subject' as keyof ExtendedVideo,
      label: 'Subject',
      render: (video: Video) => video.subject?.display_name || '-',
    },
    {
      key: 'grade' as keyof ExtendedVideo,
      label: 'Grade',
      render: (video: Video) => video.grade?.display_name || '-',
    },
    {
      key: 'title',
      label: 'Title',
      sortable: true,
    },
    {
      key: 'uploader' as keyof ExtendedVideo,
      label: 'Uploader',
      render: (video: Video) => video.uploader?.name || '-',
    },
    {
      key: 'created_at',
      label: 'Created',
      render: (video: ExtendedVideo) => new Date(video.created_at).toLocaleDateString(),
      sortable: true,
    },
    {
      key: 'video_status' as keyof ExtendedVideo,
      label: 'Status',
      render: (video: Video) => (
        <StatusBadge
          status={video.video_status.name}
          displayName={video.video_status.display_name}
        />
      ),
    },
    {
      key: 'actions' as keyof ExtendedVideo,
      label: 'Actions',
      render: (video: Video) => (
        <div className="flex space-x-2">
          <button
            onClick={() => router.push(`/videos/${video.id}`)}
            className="text-blue-600 hover:underline text-sm"
            disabled={!isActionEnabled(video)}
          >
            Edit
          </button>
          <button
            onClick={() => {
              setVideoToPreview(video);
              setPreviewModalOpen(true);
            }}
            className="text-blue-600 hover:underline text-sm"
            disabled={!isActionEnabled(video)}
          >
            Preview
          </button>
          <button
            onClick={() => {
              setVideoToDelete(video);
              setDeleteModalOpen(true);
            }}
            className="text-red-600 hover:underline text-sm"
          >
            Delete
          </button>
          {video.video_status.name === VideoStatus.VIDEO_READY && (
            <a
              href={`/video/${video.uid}`}
              target="_blank"
              rel="noopener noreferrer"
              className="text-green-600 hover:underline text-sm"
            >
              Share
            </a>
          )}
        </div>
      ),
    },
  ], [selectedVideos, videos, handleSort]);

  return (
    <div className="p-6">
      <div className="flex justify-between items-center mb-6">
        <h1 className="text-3xl font-bold">Videos</h1>
        <Button
          label="Add Video"
          variant="primary"
          onClick={() => router.push('/videos/create')}
        />
      </div>

      <div className="flex gap-4 mb-6">
        <div className="flex-1">
          <Input
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            placeholder="Search videos..."
            onKeyPress={(e: any) => e.key === 'Enter' && handleSearch()}
          />
        </div>
        <Button label="Search" onClick={handleSearch} />
        {selectedVideos.size > 0 && (
          <Button
            label={`Mass Action (${selectedVideos.size})`}
            variant="secondary"
            onClick={() => setMassActionModalOpen(true)}
          />
        )}
      </div>

      <ItemGrid
        items={videos}
        columns={columns}
        layout="list"
        loading={loading}
      />

      <Pagination
        currentPage={currentPage}
        totalPages={totalPages}
        onPageChange={setCurrentPage}
      />

      {/* Delete Confirmation Modal */}
      <Modal
        isOpen={deleteModalOpen}
        onClose={() => {
          setDeleteModalOpen(false);
          setVideoToDelete(null);
          setDeleteConfirmation('');
        }}
        title="Delete Video"
        size="md"
      >
        <div className="space-y-4">
          <p className="text-gray-700">
            Are you sure you want to delete this video? This action cannot be undone.
          </p>
          {videoToDelete && (
            <>
              <p className="text-sm text-gray-600">
                To confirm, please type the video title: <strong>{videoToDelete.title}</strong>
              </p>
              <Input
                value={deleteConfirmation}
                onChange={(e) => setDeleteConfirmation(e.target.value)}
                placeholder="Type video title to confirm"
              />
            </>
          )}
          <div className="flex justify-end space-x-3">
            <Button
              label="Cancel"
              variant="secondary"
              onClick={() => {
                setDeleteModalOpen(false);
                setVideoToDelete(null);
                setDeleteConfirmation('');
              }}
            />
            <Button
              label="Delete"
              variant="primary"
              onClick={handleDelete}
              disabled={!videoToDelete || deleteConfirmation !== videoToDelete.title}
            />
          </div>
        </div>
      </Modal>

      {/* Preview Modal */}
      <Modal
        isOpen={previewModalOpen}
        onClose={() => {
          setPreviewModalOpen(false);
          setVideoToPreview(null);
        }}
        title="Preview Video"
        size="xl"
      >
        {videoToPreview && (
          <div className="space-y-4">
            <VideoPlayer
              src={videoToPreview.private_url || videoToPreview.public_url}
              poster={videoToPreview.authenticated_poster}
              status={videoToPreview.video_status.name}
            />
            <div className="grid grid-cols-2 gap-4 text-sm">
              <div>
                <strong>Title:</strong> {videoToPreview.title}
              </div>
              <div>
                <strong>Day:</strong> {videoToPreview.day}
              </div>
              <div>
                <strong>Subject:</strong> {videoToPreview.subject?.display_name || '-'}
              </div>
              <div>
                <strong>Grade:</strong> {videoToPreview.grade?.display_name || '-'}
              </div>
              <div className="col-span-2">
                <strong>Description:</strong> {videoToPreview.description || '-'}
              </div>
            </div>
          </div>
        )}
      </Modal>

      {/* Mass Action Modal */}
      <MassActionModal
        isOpen={massActionModalOpen}
        onClose={() => setMassActionModalOpen(false)}
        selectedVideoIds={Array.from(selectedVideos)}
        onComplete={() => {
          setMassActionModalOpen(false);
          setSelectedVideos(new Set());
          loadVideos();
        }}
      />
    </div>
  );
}