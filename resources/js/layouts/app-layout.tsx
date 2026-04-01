import AppTopBarLayout from '@/layouts/app/app-top-bar-layout';
import { type TabItem } from '@/components/app-top-bar';

interface AppLayoutProps {
    children: React.ReactNode;
    tabs?: TabItem[];
}

export default function AppLayout({ children, tabs }: AppLayoutProps) {
    return (
        <AppTopBarLayout tabs={tabs}>
            {children}
        </AppTopBarLayout>
    );
}
