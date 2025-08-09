import { makeAuthenticatedRequest } from '@/lib/auth';
import {
  Video,
  VideoListResponse,
  VideoCreateRequest,
  VideoUpdateRequest,
  VideoUploadCredentials,
  MassUpdateRequest,
  Grade,
  Subject,
  VideoMode,
} from '@/types/video';

export class VideoService {
  static async listVideos(
    page: number = 1,
    search?: string,
    sort?: { field: string; direction: 'asc' | 'desc' }
  ): Promise<VideoListResponse> {
    const params = new URLSearchParams({
      page: page.toString(),
      ...(search && { search }),
      ...(sort && { sort: sort.field, order: sort.direction }),
    });

    const response = await makeAuthenticatedRequest(`/api/videos?${params.toString()}`);
    if (!response.ok) {
      throw new Error('Failed to fetch videos');
    }
    return response.json();
  }

  static async getVideo(id: number): Promise<Video> {
    const response = await makeAuthenticatedRequest(`/api/videos/${id}`);
    if (!response.ok) {
      throw new Error('Failed to fetch video');
    }
    return response.json();
  }

  static async createVideo(data: FormData): Promise<VideoUploadCredentials[]> {
    const response = await makeAuthenticatedRequest('/api/videos', {
      method: 'POST',
      body: data,
      headers: {
        // Remove Content-Type to let browser set it with boundary for multipart/form-data
        'Content-Type': undefined as any,
      },
    });
    
    if (!response.ok) {
      throw new Error('Failed to create video');
    }
    return response.json();
  }

  static async updateVideo(id: number, data: VideoUpdateRequest): Promise<Video> {
    const response = await makeAuthenticatedRequest(`/api/videos/${id}`, {
      method: 'PUT',
      body: JSON.stringify(data),
    });
    
    if (!response.ok) {
      throw new Error('Failed to update video');
    }
    return response.json();
  }

  static async deleteVideo(id: number): Promise<void> {
    const response = await makeAuthenticatedRequest(`/api/videos/${id}`, {
      method: 'DELETE',
    });
    
    if (!response.ok) {
      throw new Error('Failed to delete video');
    }
  }

  static async uploadComplete(videoIds: number[]): Promise<void> {
    const response = await makeAuthenticatedRequest('/api/videos/upload_complete', {
      method: 'POST',
      body: JSON.stringify({ video_ids: videoIds }),
    });
    
    if (!response.ok) {
      throw new Error('Failed to mark upload as complete');
    }
  }

  static async massUpdate(data: MassUpdateRequest): Promise<void> {
    const response = await makeAuthenticatedRequest('/api/videos/mass_update', {
      method: 'POST',
      body: JSON.stringify(data),
    });
    
    if (!response.ok) {
      throw new Error('Failed to update videos');
    }
  }

  static async getGrades(): Promise<Grade[]> {
    const response = await makeAuthenticatedRequest('/api/grades');
    if (!response.ok) {
      throw new Error('Failed to fetch grades');
    }
    return response.json();
  }

  static async getSubjects(): Promise<Subject[]> {
    const response = await makeAuthenticatedRequest('/api/subjects');
    if (!response.ok) {
      throw new Error('Failed to fetch subjects');
    }
    return response.json();
  }

  static async getVideoModes(): Promise<VideoMode[]> {
    const response = await makeAuthenticatedRequest('/api/video-modes');
    if (!response.ok) {
      throw new Error('Failed to fetch video modes');
    }
    return response.json();
  }
}