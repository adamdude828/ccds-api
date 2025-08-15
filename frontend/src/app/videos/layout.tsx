'use client';

import { InteractionType } from '@azure/msal-browser';
import { MsalAuthenticationTemplate, useMsal } from '@azure/msal-react';
import { useRouter, usePathname } from 'next/navigation';
import { JSX, ReactNode, useEffect } from 'react';
import { PageTemplate } from '@challenger-school/do-git-mis-components-storybook';
import { useState } from 'react';

const navItems = [
  { id: 'videos', icon: '🎥', label: 'Videos' },
  { id: 'dashboard', icon: '▶', label: 'Dashboard' },
  { id: 'documents', icon: '📄', label: 'Documents' },
];

const authRequest = {
  scopes: ["User.Read"]
};

export default function VideosLayout({
  children,
}: {
  children: ReactNode;
}): JSX.Element {
  const router = useRouter();
  const pathname = usePathname();
  const { instance } = useMsal();
  const [activeItem, setActiveItem] = useState(() => {
    const path = pathname?.split('/')[2] || 'videos';
    return path;
  });

  useEffect(() => {
    const path = pathname?.split('/')[2] || 'videos';
    setActiveItem(path);
  }, [pathname]);

  const handleNavItemClick = (itemId: string) => {
    setActiveItem(itemId);
    if (itemId === 'dashboard') {
      router.push('/dashboard');
    } else if (itemId === 'documents') {
      router.push('/documents');
    } else {
      router.push(`/videos/${itemId === 'videos' ? '' : itemId}`);
    }
  };

  const handleLogout = async () => {
    try {
      // This will clear the cache and end the server session
      await instance.logoutRedirect({
        postLogoutRedirectUri: window.location.origin,
      });
    } catch (error) {
      console.error('Logout failed:', error);
      // Fallback navigation if logout fails
      router.push('/');
    }
  };

  return (
    <MsalAuthenticationTemplate 
      interactionType={InteractionType.Redirect}
      authenticationRequest={authRequest}
      errorComponent={() => {
        router.push('/');
        return <div>Redirecting to login...</div>;
      }}
      loadingComponent={() => <div>Loading...</div>}
    >
      <PageTemplate
        projectName="Video Manager"
        navItems={navItems}
        activeItemId={activeItem}
        onNavItemClick={handleNavItemClick}
        onLogout={handleLogout}
      >
        {children}
      </PageTemplate>
    </MsalAuthenticationTemplate>
  );
}
