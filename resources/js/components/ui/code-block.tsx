import * as React from 'react';

import { cn } from '@/lib/utils';
import { Check, Copy } from 'lucide-react';

interface CodeBlockProps extends React.HTMLAttributes<HTMLDivElement> {
    label?: string;
    action?: React.ReactNode;
    copyable?: boolean;
    code?: string;
}

function CodeBlock({ className, label, action, copyable, code, children, ...props }: CodeBlockProps) {
    const [copied, setCopied] = React.useState(false);

    const handleCopy = () => {
        if (code) {
            navigator.clipboard.writeText(code);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        }
    };

    return (
        <div className={cn('overflow-hidden rounded-md border border-border', className)} {...props}>
            {(label || action || copyable) && (
                <div className="flex items-center justify-between border-b border-border bg-muted/50 px-4 py-2">
                    {label && <span className="text-muted-foreground text-xs font-medium uppercase tracking-wider">{label}</span>}
                    <div className="flex items-center gap-2">
                        {copyable && (
                            <button
                                onClick={handleCopy}
                                className="text-muted-foreground hover:text-foreground inline-flex items-center gap-1 text-xs transition-colors"
                            >
                                {copied ? <Check className="size-3" /> : <Copy className="size-3" />}
                                {copied ? 'Copied' : 'Copy'}
                            </button>
                        )}
                        {action}
                    </div>
                </div>
            )}
            <pre className="bg-[#0c1324] p-4 text-sm leading-relaxed text-[#dce1fb] overflow-x-auto">
                <code className="font-mono">{children || code}</code>
            </pre>
        </div>
    );
}

export { CodeBlock };
