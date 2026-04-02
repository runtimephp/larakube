import { SVGAttributes } from 'react';

/**
 * Kuven brand mark — an abstract "K" formed by two strokes.
 * Clean, ownable, and scales to any size.
 */
export default function KuvenLogoIcon({ color = '#059669', ...props }: SVGAttributes<SVGElement> & { color?: string }) {
    return (
        <svg {...props} viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 6 L10 34" stroke={color} strokeWidth="4" strokeLinecap="round" />
            <path d="M28 6 L10 20 L28 34" stroke={color} strokeWidth="4" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}
