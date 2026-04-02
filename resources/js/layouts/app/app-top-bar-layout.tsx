import { AppTopBar, type TabItem } from '@/components/app-top-bar';

interface AppTopBarLayoutProps {
    children: React.ReactNode;
    tabs?: TabItem[];
}

export default function AppTopBarLayout({ children, tabs }: AppTopBarLayoutProps) {
    return (
        <div className="flex min-h-screen flex-col bg-white">
            <AppTopBar tabs={tabs} />
            <main className="flex flex-1 flex-col px-2 py-px">
                <div className="border-border relative z-0 mx-auto -mt-px flex w-full grow flex-col items-stretch rounded-b-lg border-x border-b bg-white shadow-xs">
                    <div aria-hidden="true" className="pointer-events-none absolute top-0 left-0 z-10 h-4 w-4 bg-white" />
                    <div aria-hidden="true" className="pointer-events-none absolute top-0 right-0 z-10 h-4 w-4 bg-white" />
                    {children}
                </div>
            </main>
        </div>
    );
}
