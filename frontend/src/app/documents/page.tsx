'use client';

import React from 'react';
import { Button } from '@challenger-school/do-git-mis-components-storybook';

export default function DocumentsPage() {
  return (
    <div className="p-6 space-y-6">
      <div className="bg-white rounded-lg shadow p-6">
        <h2 className="text-2xl font-semibold mb-4">Document Management</h2>
        <p className="text-gray-600 mb-6">
          Welcome to the document management section. Here you can upload, organize, and access your documents.
        </p>
        
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div className="bg-gray-50 p-6 rounded-lg">
            <h3 className="text-lg font-medium text-gray-900 mb-2">Total Documents</h3>
            <p className="text-3xl font-bold text-blue-600">0</p>
            <p className="text-sm text-gray-500 mt-1">Documents uploaded</p>
          </div>
          
          <div className="bg-gray-50 p-6 rounded-lg">
            <h3 className="text-lg font-medium text-gray-900 mb-2">Storage Used</h3>
            <p className="text-3xl font-bold text-green-600">0 MB</p>
            <p className="text-sm text-gray-500 mt-1">Of available space</p>
          </div>
          
          <div className="bg-gray-50 p-6 rounded-lg">
            <h3 className="text-lg font-medium text-gray-900 mb-2">Recent Activity</h3>
            <p className="text-3xl font-bold text-purple-600">0</p>
            <p className="text-sm text-gray-500 mt-1">This week</p>
          </div>
        </div>

        <div className="border-t pt-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
          <div className="flex flex-wrap gap-4">
            <Button
              label="Upload Document"
              variant="primary"
              onClick={() => console.log('Upload document clicked')}
            />
            <Button
              label="View Library"
              variant="secondary"
              onClick={() => console.log('View library clicked')}
            />
            <Button
              label="Settings"
              variant="secondary"
              onClick={() => console.log('Settings clicked')}
            />
          </div>
        </div>
      </div>

      <div className="bg-white rounded-lg shadow p-6">
        <h3 className="text-lg font-medium text-gray-900 mb-4">Recent Documents</h3>
        <div className="text-center py-8">
          <div className="text-gray-400 text-6xl mb-4">📄</div>
          <p className="text-gray-500 text-lg mb-2">No documents uploaded yet</p>
          <p className="text-gray-400 text-sm">Upload your first document to get started</p>
        </div>
      </div>
    </div>
  );
}
