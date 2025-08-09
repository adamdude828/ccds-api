'use client';

import { InteractionType } from '@azure/msal-browser';
import { MsalAuthenticationTemplate, useMsal } from '@azure/msal-react';
import { useRouter, usePathname } from 'next/navigation';
import { JSX, ReactNode, useEffect } from 'react';
import { PageTemplate } from '@challenger-school/do-git-mis-components-storybook';
import { useState } from 'react';
import { MdVideoLibrary, MdDescription } from 'react-icons/md';

const navItems = [
  { id: 'videos', icon: <MdVideoLibrary />, label: 'Videos' },
  { id: 'documents', icon: <MdDescription />, label: 'Documents' },
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
    const path = pathname?.split('/')[1] || 'videos';
    return path;
  });

  useEffect(() => {
    const path = pathname?.split('/')[1] || 'videos';
    setActiveItem(path);
  }, [pathname]);

  const handleNavItemClick = (itemId: string) => {
    setActiveItem(itemId);
    router.push(`/${itemId}`);
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
        return <div>Authentication required. Redirecting to login...</div>;
      }}
      loadingComponent={() => <div>Loading...</div>}
    >
      <PageTemplate
        projectName="CCDS"
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