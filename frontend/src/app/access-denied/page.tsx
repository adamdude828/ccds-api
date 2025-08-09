'use client';

import { Button } from '@challenger-school/do-git-mis-components-storybook';
import Link from 'next/link';

export default function AccessDenied() {
  return (
    <div className="min-h-screen flex flex-col items-center justify-center p-4">
      <div className="bg-white p-8 rounded-lg shadow-md max-w-md w-full text-center">
        <div className="mb-6">
          <h1 className="text-2xl font-bold text-red-600 mb-2">Access Denied</h1>
          <p className="text-gray-600">
            You do not have permission to access this application. 
            Please contact your administrator if you believe this is a mistake.
          </p>
        </div>
        
        <div className="flex justify-center">
          <Link href="/" passHref>
            <Button
              label="Return to Home"
              variant="secondary"
              className="mx-2"
            />
          </Link>
        </div>
      </div>
    </div>
  );
} 