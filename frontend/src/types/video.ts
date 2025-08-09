export enum VideoStatus {
  VIDEO_READY = 'video_ready',
  DRAFT = 'draft',
  UPLOAD_IN_PROGRESS = 'upload_in_progress',
  UPLOAD_COMPLETE = 'upload_complete',
  QUEUED_TRANSCODE = 'queued_transcode',
  TRANSCODE_IN_PROGRESS = 'transcode_in_progress',
  WAITING_FOR_POSTER = 'waiting_for_poster',
  POSTER_IN_PROGRESS = 'poster_in_progress',
  FAILED = 'Failed'
}

export interface VideoStatusDetail {
  name: VideoStatus;
  display_name: string;
}

export interface VideoMode {
  id: number;
  name: string;
  display_name: string;
}

export interface Grade {
  id: number;
  name: string;
  display_name: string;
}

export interface Subject {
  id: number;
  name: string;
  display_name: string;
}

export interface User {
  id: number;
  name: string;
  email: string;
}

export interface VideoMetadata {
  container_name?: string;
  blob_name?: string;
  upload_url?: string;
  size?: number;
  duration?: number;
}

export interface Video {
  id: number;
  file: string;
  subject_id: number;
  grade_id: number;
  video_mode_id: number;
  day: number;
  description: string;
  meta: VideoMetadata;
  public_url: string;
  private_url: string;
  authenticated_poster: string;
  title: string;
  uid: string;
  video_status: VideoStatusDetail;
  created_at: string;
  updated_at: string;
  deleted_at?: string;
  
  // Relations
  subject?: Subject;
  grade?: Grade;
  video_mode?: VideoMode;
  uploader?: User;
}

export interface VideoCreateRequest {
  file: File;
  poster?: File;
  subject_id: number;
  grade_id: number;
  day: number;
  description?: string;
}

export interface VideoUpdateRequest {
  title?: string;
  subject_id?: number;
  grade_id?: number;
  video_mode_id?: number;
  day?: number;
  description?: string;
}

export interface VideoUploadCredentials {
  video_id: number;
  upload_url: string;
  container_name: string;
  blob_name: string;
}

export interface VideoListResponse {
  data: Video[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  from: number;
  to: number;
}

export interface MassUpdateRequest {
  video_ids: number[];
  subject_id?: number;
  grade_id?: number;
  video_mode_id?: number;
  day?: number;
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
}