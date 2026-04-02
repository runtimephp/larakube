import { Link } from '@inertiajs/react';

interface KuvenLogoProps {
    href?: string;
    showWordmark?: boolean;
}

export default function KuvenLogo({ href = '/', showWordmark = true }: KuvenLogoProps) {
    const content = (
        <span className="flex items-center gap-2">
            {showWordmark && <span className="text-xl font-semibold tracking-wide text-[#1b1b18] dark:text-[#EDEDEC]">Kuven</span>}
        </span>
    );

    return (
        <Link href={href} className="focus-visible:outline-none">
            {content}
        </Link>
    );
}
