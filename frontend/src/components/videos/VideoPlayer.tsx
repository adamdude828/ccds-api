'use client';

import React, { useEffect, useRef } from 'react';
import { VideoStatus } from '@/types/video';

interface VideoPlayerProps {
  src: string;
  poster?: string;
  status: VideoStatus;
  className?: string;
}

export default function VideoPlayer({ src, poster, status, className = '' }: VideoPlayerProps) {
  const videoRef = useRef<HTMLVideoElement>(null);
  const playerInitialized = useRef(false);

  useEffect(() => {
    const loadVideoJS = async () => {
      if (!videoRef.current || playerInitialized.current) return;

      try {
        const videojs = (await import('video.js')).default;

        const player = videojs(videoRef.current, {
          controls: true,
          fluid: true,
          preload: 'auto',
          poster,
        });

        playerInitialized.current = true;

        // Setup HLS if video is ready
        if (status === VideoStatus.VIDEO_READY && src.includes('.m3u8')) {
          player.src({
            src,
            type: 'application/x-mpegURL',
          });
        } else {
          // Use direct MP4 for draft videos
          player.src({
            src,
            type: 'video/mp4',
          });
        }

        return () => {
          if (player) {
            player.dispose();
            playerInitialized.current = false;
          }
        };
      } catch (error) {
        console.error('Error loading video.js:', error);
      }
    };

    loadVideoJS();
  }, [src, poster, status]);

  return (
    <div className={`video-player-wrapper ${className}`}>
      <video
        ref={videoRef}
        className="video-js vjs-default-skin vjs-big-play-centered"
        playsInline
      />
    </div>
  );
}