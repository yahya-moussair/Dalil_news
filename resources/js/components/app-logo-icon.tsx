import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
            <path
                fill="currentColor"
                fillRule="evenodd"
                d="M7 6.5h13.2a5.2 5.2 0 0 1 5.2 5.2v6.1a5.2 5.2 0 0 1-5.2 5.2H14l-5.2 4.1v-4.1H7a5.2 5.2 0 0 1-5.2-5.2v-6.1A5.2 5.2 0 0 1 7 6.5Zm4.2 6.95a1.35 1.35 0 1 0 0 2.7 1.35 1.35 0 0 0 0-2.7Zm4.8 0a1.35 1.35 0 1 0 0 2.7 1.35 1.35 0 0 0 0-2.7Zm4.8 0a1.35 1.35 0 1 0 0 2.7 1.35 1.35 0 0 0 0-2.7Z"
            />
        </svg>
    );
}
