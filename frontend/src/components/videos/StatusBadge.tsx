import React from 'react';
import Badge from '@/components/common/Badge';
import { VideoStatus } from '@/types/video';

interface StatusBadgeProps {
  status: VideoStatus;
  displayName: string;
}

export default function StatusBadge({ status, displayName }: StatusBadgeProps) {
  const getVariant = () => {
    switch (status) {
      case VideoStatus.VIDEO_READY:
        return 'success';
      case VideoStatus.DRAFT:
        return 'info';
      case VideoStatus.UPLOAD_IN_PROGRESS:
      case VideoStatus.UPLOAD_COMPLETE:
      case VideoStatus.QUEUED_TRANSCODE:
      case VideoStatus.TRANSCODE_IN_PROGRESS:
      case VideoStatus.WAITING_FOR_POSTER:
      case VideoStatus.POSTER_IN_PROGRESS:
        return 'warning';
      case VideoStatus.FAILED:
        return 'error';
      default:
        return 'default';
    }
  };

  return <Badge text={displayName} variant={getVariant()} />;
}