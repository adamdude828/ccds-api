export interface UploadProgress {
  loaded: number;
  total: number;
  percentage: number;
}

export class AzureStorageService {
  private static readonly BLOCK_SIZE = 512 * 1024; // 512KB chunks

  static async uploadToBlob(
    file: File,
    uploadUrl: string,
    onProgress?: (progress: UploadProgress) => void
  ): Promise<void> {
    const fileSize = file.size;
    const chunks = Math.ceil(fileSize / this.BLOCK_SIZE);
    const blockIds: string[] = [];

    // Upload chunks
    for (let i = 0; i < chunks; i++) {
      const start = i * this.BLOCK_SIZE;
      const end = Math.min(start + this.BLOCK_SIZE, fileSize);
      const chunk = file.slice(start, end);
      
      const blockId = btoa(`block-${i.toString().padStart(6, '0')}`);
      blockIds.push(blockId);

      await this.uploadBlock(uploadUrl, chunk, blockId);

      if (onProgress) {
        const loaded = Math.min(end, fileSize);
        onProgress({
          loaded,
          total: fileSize,
          percentage: Math.round((loaded / fileSize) * 100),
        });
      }
    }

    // Commit blocks
    await this.commitBlocks(uploadUrl, blockIds);
  }

  private static async uploadBlock(
    uploadUrl: string,
    chunk: Blob,
    blockId: string
  ): Promise<void> {
    const url = new URL(uploadUrl);
    url.searchParams.append('comp', 'block');
    url.searchParams.append('blockid', blockId);

    const response = await fetch(url.toString(), {
      method: 'PUT',
      body: chunk,
      headers: {
        'x-ms-blob-type': 'BlockBlob',
      },
    });

    if (!response.ok) {
      throw new Error(`Failed to upload block: ${response.statusText}`);
    }
  }

  private static async commitBlocks(
    uploadUrl: string,
    blockIds: string[]
  ): Promise<void> {
    const url = new URL(uploadUrl);
    url.searchParams.append('comp', 'blocklist');

    const blockListXml = this.generateBlockListXml(blockIds);

    const response = await fetch(url.toString(), {
      method: 'PUT',
      body: blockListXml,
      headers: {
        'Content-Type': 'application/xml',
      },
    });

    if (!response.ok) {
      throw new Error(`Failed to commit blocks: ${response.statusText}`);
    }
  }

  private static generateBlockListXml(blockIds: string[]): string {
    let xml = '<?xml version="1.0" encoding="utf-8"?><BlockList>';
    for (const blockId of blockIds) {
      xml += `<Latest>${blockId}</Latest>`;
    }
    xml += '</BlockList>';
    return xml;
  }
}