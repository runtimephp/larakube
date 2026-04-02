import { AppTopBar, type TabItem } from '@/components/app-top-bar';

interface AppTopBarLayoutProps {
    children: React.ReactNode;
    tabs?: TabItem[];
}

export default function AppTopBarLayout({ children, tabs }: AppTopBarLayoutProps) {
    return (
        <div className="flex min-h-screen flex-col bg-background">
            <AppTopBar tabs={tabs} />
            <main className="mx-auto flex w-full max-w-[1920px] flex-1 flex-col px-6 py-8 sm:px-12">
                {children}
            </main>
        </div>
    );
}
