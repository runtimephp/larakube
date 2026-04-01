import { AppTopBar, type TabItem } from '@/components/app-top-bar';

interface AppTopBarLayoutProps {
    children: React.ReactNode;
    tabs?: TabItem[];
}

export default function AppTopBarLayout({ children, tabs }: AppTopBarLayoutProps) {
    return (
        <div className="bg-background flex min-h-screen flex-col">
            <AppTopBar tabs={tabs} />
            <main className="flex flex-1 flex-col">
                {children}
            </main>
        </div>
    );
}
