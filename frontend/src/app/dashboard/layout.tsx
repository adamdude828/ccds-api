'use client';

import { InteractionType } from '@azure/msal-browser';
import { MsalAuthenticationTemplate, useMsal } from '@azure/msal-react';
import { useRouter, usePathname } from 'next/navigation';
import { JSX, ReactNode, useEffect } from 'react';
import { PageTemplate } from '@challenger-school/do-git-mis-components-storybook';
import { useState } from 'react';
import { MdBusiness } from 'react-icons/md';

const navItems = [
  { id: 'dashboard', icon: '▶', label: 'Dashboard' },
  { id: 'campaign-reviews', icon: '📢', label: 'Campaigns' },
  { id: 'providers', icon: '📦', label: 'Providers' },
  { id: 'campuses', label: 'Campuses', icon: <MdBusiness /> },
  { id: 'email-settings', icon: '✉️', label: 'Email Settings' },
];

const authRequest = {
  scopes: ["User.Read"]
};

export default function DashboardLayout({
  children,
}: {
  children: ReactNode;
}): JSX.Element {
  const router = useRouter();
  const pathname = usePathname();
  const { instance } = useMsal();
  const [activeItem, setActiveItem] = useState(() => {
    const path = pathname?.split('/')[2] || 'dashboard';
    return path;
  });

  useEffect(() => {
    const path = pathname?.split('/')[2] || 'dashboard';
    setActiveItem(path);
  }, [pathname]);

  const handleNavItemClick = (itemId: string) => {
    setActiveItem(itemId);
    router.push(`/dashboard/${itemId === 'dashboard' ? '' : itemId}`);
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
        projectName="Solicit Reviews"
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
