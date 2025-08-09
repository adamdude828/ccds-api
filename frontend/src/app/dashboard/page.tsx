'use client';

import React from 'react';
import { Button } from '@challenger-school/do-git-mis-components-storybook';

export default function DashboardPage() {
  return (
    <div className="p-6 space-y-6">
      <div className="bg-white rounded-lg shadow p-6">
        <h2 className="text-2xl font-semibold mb-4">Welcome to Your Dashboard</h2>
        <p className="text-gray-600 mb-6">
          This is your main dashboard. You can customize this page to display your application&apos;s key metrics and features.
        </p>
        
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
          <div className="bg-gray-50 p-6 rounded-lg">
            <h3 className="text-lg font-medium text-gray-900 mb-2">Quick Stats</h3>
            <p className="text-3xl font-bold text-blue-600">0</p>
            <p className="text-sm text-gray-500 mt-1">Total Items</p>
          </div>
          
          <div className="bg-gray-50 p-6 rounded-lg">
            <h3 className="text-lg font-medium text-gray-900 mb-2">Activity</h3>
            <p className="text-3xl font-bold text-green-600">0</p>
            <p className="text-sm text-gray-500 mt-1">This Month</p>
          </div>
          
          <div className="bg-gray-50 p-6 rounded-lg">
            <h3 className="text-lg font-medium text-gray-900 mb-2">Performance</h3>
            <p className="text-3xl font-bold text-purple-600">0%</p>
            <p className="text-sm text-gray-500 mt-1">Success Rate</p>
          </div>
        </div>

        <div className="border-t pt-6">
          <h3 className="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
          <div className="flex flex-wrap gap-4">
            <Button
              label="Create New"
              variant="primary"
              onClick={() => console.log('Create new clicked')}
            />
            <Button
              label="View Reports"
              variant="secondary"
              onClick={() => console.log('View reports clicked')}
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
        <h3 className="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
        <p className="text-gray-500">No recent activity to display.</p>
      </div>
    </div>
  );
}
