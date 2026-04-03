import { AwsLogo } from '@/components/icons/aws-logo';
import { ValkeyLogo } from '@/components/icons/valkey-logo';
import {
    SiAkamai,
    SiDigitalocean,
    SiDjango,
    SiExpress,
    SiFastapi,
    SiGo,
    SiHetzner,
    SiKubernetes,
    SiLaravel,
    SiNestjs,
    SiNextdotjs,
    SiNuxt,
    SiRubyonrails,
    SiSpringboot,
    SiVultr,
} from '@icons-pack/react-simple-icons';
import { Head, useForm, usePage } from '@inertiajs/react';
import { AppWindow, Bolt, Cloud, Database, Globe, HardDrive, Loader2, Radio, Server, ShieldCheck } from 'lucide-react';
import { type FormEventHandler, useEffect, useRef, useState } from 'react';

const applications = [
    {
        icon: <AppWindow className="size-4" />,
        name: 'web-app',
        meta: 'Laravel • 3 pods',
        status: 'Running',
        badgeClass: 'bg-emerald-500/10 text-emerald-400',
    },
    {
        icon: <Bolt className="size-4" />,
        name: 'scheduler',
        meta: 'Laravel • 1 pod',
        status: 'Running',
        badgeClass: 'bg-emerald-500/10 text-emerald-400',
    },
    {
        icon: <Server className="size-4" />,
        name: 'queue-worker',
        meta: 'Laravel • 2 pods',
        status: 'Running',
        badgeClass: 'bg-emerald-500/10 text-emerald-400',
    },
    {
        icon: <Radio className="size-4" />,
        name: 'websockets',
        meta: 'Reverb • 1 pod',
        status: 'Running',
        badgeClass: 'bg-emerald-500/10 text-emerald-400',
    },
];

const resources = [
    {
        icon: <Database className="size-4" />,
        name: 'postgres-18',
        meta: 'Database • eu-central',
        status: 'Provisioned',
        badgeClass: 'bg-[#d0bcff]/10 text-[#d0bcff]',
    },
    {
        icon: <ValkeyLogo className="size-4" />,
        name: 'valkey',
        meta: 'Cache • eu-central',
        status: 'Provisioned',
        badgeClass: 'bg-[#d0bcff]/10 text-[#d0bcff]',
    },
    {
        icon: <HardDrive className="size-4" />,
        name: 'object-storage',
        meta: 'S3 • eu-central',
        status: 'Provisioned',
        badgeClass: 'bg-[#d0bcff]/10 text-[#d0bcff]',
    },
    {
        icon: <Radio className="size-4" />,
        name: 'redis-queue',
        meta: 'Queue backend',
        status: 'Provisioned',
        badgeClass: 'bg-[#d0bcff]/10 text-[#d0bcff]',
    },
];

const features = [
    {
        icon: <Cloud className="size-6" />,
        title: 'Any Cloud, One Interface',
        desc: 'Deploy to Hetzner, DigitalOcean, or AWS. Your infrastructure, managed by Kuven.',
    },
    {
        icon: <ShieldCheck className="size-6" />,
        title: 'Zero-Config Security',
        desc: 'RBAC isolation, encrypted secrets, and network policies from day one.',
    },
    {
        icon: <Globe className="size-6" />,
        title: 'Production in Minutes',
        desc: 'From git push to live cluster. No YAML, no kubectl, no complexity.',
    },
];

const providers = [
    { icon: <SiHetzner className="size-4" />, bg: 'bg-[#d50c2d]' },
    { icon: <SiDigitalocean className="size-4" />, bg: 'bg-[#0080ff]' },
    { icon: <AwsLogo className="size-4" />, bg: 'bg-[#232f3e] text-[#ff9900]' },
    { icon: <SiKubernetes className="size-4" />, bg: 'bg-[#326ce5]' },
];

const supportedStacks = [
    { icon: <SiLaravel className="size-7" />, name: 'Laravel', bg: 'bg-[#FF2D20]', color: 'text-white' },
    { icon: <SiNextdotjs className="size-7" />, name: 'Next.js', bg: 'bg-white', color: 'text-black' },
    { icon: <SiNuxt className="size-7" />, name: 'Nuxt', bg: 'bg-[#00DC82]', color: 'text-white' },
    { icon: <SiDjango className="size-7" />, name: 'Django', bg: 'bg-[#44B78B]', color: 'text-white' },
    { icon: <SiFastapi className="size-7" />, name: 'FastAPI', bg: 'bg-[#009688]', color: 'text-white' },
    { icon: <SiGo className="size-7" />, name: 'Go', bg: 'bg-[#00ADD8]', color: 'text-white' },
    { icon: <SiRubyonrails className="size-7" />, name: 'Rails', bg: 'bg-[#D30001]', color: 'text-white' },
    { icon: <SiSpringboot className="size-7" />, name: 'Spring Boot', bg: 'bg-[#6DB33F]', color: 'text-white' },
    { icon: <SiExpress className="size-7" />, name: 'Express', bg: 'bg-white', color: 'text-black' },
    { icon: <SiNestjs className="size-7" />, name: 'NestJS', bg: 'bg-[#E0234E]', color: 'text-white' },
];

const cloudProviders = [
    { icon: <SiHetzner className="size-7" />, name: 'Hetzner', bg: 'bg-[#d50c2d]', color: 'text-white' },
    { icon: <SiDigitalocean className="size-7" />, name: 'DigitalOcean', bg: 'bg-[#0080ff]', color: 'text-white' },
    { icon: <AwsLogo className="size-7" />, name: 'AWS', bg: 'bg-[#232f3e]', color: 'text-[#ff9900]' },
    { icon: <SiVultr className="size-7" />, name: 'Vultr', bg: 'bg-[#007bfc]', color: 'text-white' },
    { icon: <SiAkamai className="size-7" />, name: 'Akamai', bg: 'bg-[#0096d6]', color: 'text-white' },
];

export default function Welcome() {
    const { waitlistCount } = usePage<{ waitlistCount: number }>().props;
    const flash = usePage().props.flash as Record<string, boolean> | undefined;
    const joined = flash?.waitlist_success;
    const stackCarouselRef = useRef<HTMLDivElement | null>(null);
    const dashboardSectionRef = useRef<HTMLElement | null>(null);
    const hasStartedPreviewSequenceRef = useRef(false);
    const [webAppPods, setWebAppPods] = useState(2);
    const [isWebAppScaling, setIsWebAppScaling] = useState(true);
    const [isObjectStorageProvisioned, setIsObjectStorageProvisioned] = useState(false);

    const { data, setData, post, processing, errors } = useForm({ email: '' });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('waitlist.store'));
    };

    useEffect(() => {
        const dashboardSection = dashboardSectionRef.current;
        if (!dashboardSection) {
            return;
        }

        const sequenceTimers: number[] = [];
        const startPreviewSequence = () => {
            if (hasStartedPreviewSequenceRef.current) {
                return;
            }

            hasStartedPreviewSequenceRef.current = true;
            setWebAppPods(2);
            setIsWebAppScaling(true);
            setIsObjectStorageProvisioned(false);

            sequenceTimers.push(
                window.setTimeout(() => {
                    setWebAppPods(3);
                }, 1_200),
            );
            sequenceTimers.push(
                window.setTimeout(() => {
                    setWebAppPods(4);
                }, 2_400),
            );
            sequenceTimers.push(
                window.setTimeout(() => {
                    setWebAppPods(5);
                }, 3_600),
            );
            sequenceTimers.push(
                window.setTimeout(() => {
                    setIsWebAppScaling(false);
                }, 4_800),
            );
            sequenceTimers.push(
                window.setTimeout(() => {
                    setIsObjectStorageProvisioned(true);
                }, 20_000),
            );
        };

        const observer = new IntersectionObserver(
            (entries) => {
                const isVisible = entries.some((entry) => entry.isIntersecting);
                if (isVisible) {
                    startPreviewSequence();
                    observer.disconnect();
                }
            },
            { threshold: 0.35 },
        );

        observer.observe(dashboardSection);
        return () => {
            observer.disconnect();
            sequenceTimers.forEach((timerId) => {
                window.clearTimeout(timerId);
            });
        };
    }, []);

    useEffect(() => {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        const speed = 0.03;
        const initializeScroller = (element: HTMLDivElement | null): (() => void) | null => {
            if (!element) {
                return null;
            }

            let frameId = 0;
            let lastTimestamp = 0;
            const tick = (timestamp: number) => {
                if (!lastTimestamp) {
                    lastTimestamp = timestamp;
                }

                const delta = timestamp - lastTimestamp;
                lastTimestamp = timestamp;
                const midpoint = element.scrollWidth / 2;

                element.scrollLeft += speed * delta;
                if (element.scrollLeft >= midpoint) {
                    element.scrollLeft -= midpoint;
                }

                frameId = window.requestAnimationFrame(tick);
            };

            frameId = window.requestAnimationFrame(tick);
            return () => {
                window.cancelAnimationFrame(frameId);
            };
        };

        const cleanupStack = initializeScroller(stackCarouselRef.current);

        return () => {
            cleanupStack?.();
        };
    }, []);

    return (
        <div className="kuven-dark dark min-h-screen bg-[#0c1324] text-[#dce1fb]">
            <Head title="Your Code. Any Cloud. We Handle the Rest." />

            {/* Nav */}
            <nav className="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-5 sm:px-6 sm:py-6">
                <span className="font-headline shrink-0 text-xl font-black tracking-tighter text-[#d0bcff] sm:text-2xl">Kuven</span>
                <div className="grid grid-cols-4 gap-1.5 sm:flex sm:items-center sm:gap-2">
                    {providers.map((p, i) => (
                        <div key={i} className={`flex size-7 items-center justify-center rounded-md text-white sm:size-8 sm:rounded-lg ${p.bg}`}>
                            {p.icon}
                        </div>
                    ))}
                </div>
            </nav>

            {/* Hero */}
            <section className="mx-auto max-w-5xl px-4 pt-10 pb-16 sm:px-6 sm:pt-14 sm:pb-20">
                <div className="mb-6 inline-flex items-center gap-2 rounded-full border border-[#d0bcff]/20 bg-[#d0bcff]/10 px-4 py-1.5 text-[10px] font-bold tracking-widest text-[#d0bcff] uppercase sm:mb-8 sm:text-xs">
                    Early Access
                </div>

                <h1 className="font-headline motion-safe:animate-in motion-safe:fade-in-0 motion-safe:slide-in-from-bottom-1 max-w-3xl text-3xl leading-[1.1] font-extrabold tracking-tight motion-safe:[animation-delay:80ms] motion-safe:[animation-duration:500ms] motion-safe:[animation-fill-mode:both] sm:text-5xl md:text-7xl">
                    Your Code. Any Cloud.
                    <br />
                    <span className="text-[#d0bcff]">We Handle the Rest.</span>
                </h1>

                <p className="motion-safe:animate-in motion-safe:fade-in-0 mt-5 max-w-xl text-base leading-relaxed text-[#cbc3d7] motion-safe:[animation-delay:150ms] motion-safe:[animation-duration:450ms] motion-safe:[animation-fill-mode:both] sm:mt-6 sm:text-lg">
                    Scale Laravel, Next.js, Nuxt, Django, FastAPI, and Go on K8s with zero YAML. Kuven abstracts the complexity into a unified control
                    plane for high-velocity engineering.
                </p>

                {/* Waitlist form */}
                <div className="motion-safe:animate-in motion-safe:fade-in-0 mt-8 max-w-xl motion-safe:[animation-delay:240ms] motion-safe:[animation-duration:450ms] motion-safe:[animation-fill-mode:both] sm:mt-10">
                    {joined ? (
                        <div className="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-sm text-emerald-400 sm:px-6">
                            You're on the list. We'll be in touch.
                        </div>
                    ) : (
                        <form onSubmit={submit} className="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <input
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="you@company.com"
                                required
                                className="w-full rounded-lg border border-[#494454] bg-[#151b2d] px-4 py-3 text-sm text-[#dce1fb] placeholder:text-[#6b6b80] focus:border-[#d0bcff]/30 focus:ring-[3px] focus:ring-[#d0bcff]/25 focus:outline-none sm:flex-1"
                            />
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-br from-[#d0bcff] to-[#a078ff] px-6 py-3 text-sm font-semibold text-[#3c0091] transition-all active:scale-95 disabled:opacity-50 sm:w-auto"
                            >
                                {processing && <Loader2 className="size-4 animate-spin" />}
                                Join Waitlist
                            </button>
                        </form>
                    )}
                    {errors.email && <p className="mt-2 text-sm text-[#ffb4ab]">{errors.email}</p>}

                    {waitlistCount > 0 && (
                        <p className="mt-4 text-xs text-[#cbc3d7]/60">
                            {waitlistCount.toLocaleString()} {waitlistCount === 1 ? 'person' : 'people'} on the waitlist
                        </p>
                    )}
                </div>
            </section>

            {/* Mock Dashboard */}
            <section ref={dashboardSectionRef} className="mx-auto max-w-5xl px-4 pb-20 sm:px-6 sm:pb-24">
                <div className="overflow-hidden rounded-xl border border-[#494454]/30 bg-[#151b2d] shadow-2xl shadow-[#d0bcff]/5 sm:rounded-2xl">
                    {/* Window chrome */}
                    <div className="flex items-center gap-3 border-b border-[#494454]/20 px-4 py-3 sm:gap-4 sm:px-5">
                        <div className="flex gap-1.5">
                            <div className="size-2.5 rounded-full bg-[#ffb4ab]/40" />
                            <div className="size-2.5 rounded-full bg-[#ffb869]/40" />
                            <div className="size-2.5 rounded-full bg-[#d0bcff]/40" />
                        </div>
                        <span className="text-[9px] font-medium tracking-widest text-[#cbc3d7]/40 uppercase sm:text-[10px]">
                            Service Catalog / Production Cluster
                        </span>
                    </div>

                    <div className="grid gap-4 p-4 sm:p-6 md:grid-cols-[1fr_auto_1fr] md:items-start md:gap-0">
                        {/* Applications */}
                        <div>
                            <p className="mb-3 text-[10px] font-bold tracking-widest text-[#cbc3d7]/40 uppercase">Applications</p>
                            <div className="space-y-2">
                                {applications.map((s) => {
                                    const isWebApp = s.name === 'web-app';
                                    const isScaling = isWebApp && isWebAppScaling;
                                    const metaText = isWebApp ? `Laravel • ${webAppPods} pods` : s.meta;
                                    const statusLabel = isScaling ? 'Scaling' : s.status;
                                    const badgeClass = isScaling ? 'bg-amber-400/15 text-amber-300 motion-safe:animate-pulse' : s.badgeClass;
                                    const iconClass = isScaling
                                        ? 'bg-amber-400/15 text-amber-300 motion-safe:animate-pulse'
                                        : 'bg-emerald-500/10 text-emerald-400';

                                    return (
                                        <div
                                            key={s.name}
                                            className="flex flex-wrap items-start gap-2 rounded-lg bg-[#0c1324] p-3 transition-colors hover:bg-[#1a2139] sm:items-center sm:gap-3"
                                        >
                                            <div className={`flex size-8 shrink-0 items-center justify-center rounded ${iconClass}`}>{s.icon}</div>
                                            <div className="min-w-0 flex-1 pr-2">
                                                <h3 className="font-headline text-sm font-bold text-[#dce1fb]">{s.name}</h3>
                                                <p className="text-xs text-[#cbc3d7]/60">{metaText}</p>
                                            </div>
                                            <span
                                                className={`ml-auto shrink-0 rounded px-2 py-0.5 text-[9px] font-bold tracking-tighter uppercase ${badgeClass}`}
                                            >
                                                {statusLabel}
                                            </span>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>

                        {/* Mobile connection dots */}
                        <div className="flex items-center justify-center gap-1 py-1 md:hidden">
                            {[...Array(8)].map((_, i) => (
                                <div key={i} className="size-1.5 rounded-full bg-[#d0bcff]" style={{ opacity: 0.15 + Math.sin(i * 0.7) * 0.2 }} />
                            ))}
                        </div>

                        {/* Desktop connection dots */}
                        <div className="hidden flex-col items-center justify-center self-stretch px-6 py-10 md:flex">
                            {[...Array(12)].map((_, i) => (
                                <div
                                    key={i}
                                    className="my-[3px] size-1.5 rounded-full bg-[#d0bcff]"
                                    style={{ opacity: 0.15 + Math.sin(i * 0.5) * 0.15 }}
                                />
                            ))}
                        </div>

                        {/* Resources */}
                        <div>
                            <p className="mb-3 text-[10px] font-bold tracking-widest text-[#cbc3d7]/40 uppercase">Resources</p>
                            <div className="space-y-2">
                                {resources.map((s) => {
                                    const isObjectStorage = s.name === 'object-storage';
                                    const isProvisioning = isObjectStorage && !isObjectStorageProvisioned;
                                    const statusLabel = isProvisioning ? 'Provisioning' : s.status;
                                    const badgeClass = isProvisioning ? 'bg-amber-400/15 text-amber-300 motion-safe:animate-pulse' : s.badgeClass;
                                    const iconClass = isProvisioning ? 'bg-amber-400/15 text-amber-300' : 'bg-[#d0bcff]/10 text-[#d0bcff]';

                                    return (
                                        <div
                                            key={s.name}
                                            className="flex flex-wrap items-start gap-2 rounded-lg bg-[#0c1324] p-3 transition-colors hover:bg-[#1a2139] sm:items-center sm:gap-3"
                                        >
                                            <div className={`flex size-8 shrink-0 items-center justify-center rounded ${iconClass}`}>{s.icon}</div>
                                            <div className="min-w-0 flex-1 pr-2">
                                                <h3 className="font-headline text-sm font-bold text-[#dce1fb]">{s.name}</h3>
                                                <p className="text-xs text-[#cbc3d7]/60">{s.meta}</p>
                                            </div>
                                            <span
                                                className={`ml-auto shrink-0 rounded px-2 py-0.5 text-[9px] font-bold tracking-tighter uppercase ${badgeClass}`}
                                            >
                                                {statusLabel}
                                            </span>
                                        </div>
                                    );
                                })}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Supported Stacks */}
            <section className="mx-auto max-w-5xl px-4 pb-20 text-center sm:px-6 sm:pb-24">
                <p className="mb-8 text-xs font-bold tracking-widest text-[#d0bcff]/60 uppercase">Built for your stack</p>
                <div className="md:hidden">
                    <div ref={stackCarouselRef} className="-mx-4 overflow-x-auto px-4 pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                        <div className="flex snap-x snap-mandatory gap-3">
                            {[...supportedStacks, ...supportedStacks].map((s, index) => (
                                <div
                                    key={`${s.name}-${index}`}
                                    className="min-w-[132px] snap-start rounded-2xl border border-[#494454]/30 bg-[#151b2d] p-4"
                                >
                                    <div className={`mx-auto flex size-12 items-center justify-center rounded-xl ${s.bg} ${s.color}`}>{s.icon}</div>
                                    <span className="mt-2 block text-xs font-medium text-[#cbc3d7]/80">{s.name}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
                <div className="mx-auto hidden max-w-3xl grid-cols-2 gap-x-3 gap-y-5 md:grid lg:grid-cols-5">
                    {supportedStacks.map((s) => (
                        <div key={s.name} className="flex flex-col items-center gap-2">
                            <div className={`flex size-12 items-center justify-center rounded-xl lg:size-14 ${s.bg} ${s.color}`}>{s.icon}</div>
                            <span className="text-xs font-medium text-[#cbc3d7]/70">{s.name}</span>
                        </div>
                    ))}
                </div>
            </section>

            {/* Bring Your Own Cloud */}
            <section className="mx-auto max-w-5xl px-4 pb-20 text-center sm:px-6 sm:pb-24">
                <p className="mb-8 text-xs font-bold tracking-widest text-[#d0bcff]/60 uppercase">Bring your own cloud</p>
                <div className="md:hidden">
                    <div className="-mx-4 overflow-x-auto px-4 pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                        <div className="flex snap-x snap-mandatory gap-3">
                            {cloudProviders.map((p) => (
                                <div key={p.name} className="min-w-[132px] snap-start rounded-2xl border border-[#494454]/30 bg-[#151b2d] p-4">
                                    <div className={`mx-auto flex size-12 items-center justify-center rounded-xl ${p.bg} ${p.color}`}>{p.icon}</div>
                                    <span className="mt-2 block text-xs font-medium text-[#cbc3d7]/80">{p.name}</span>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
                <div className="mx-auto hidden max-w-3xl grid-cols-2 gap-x-3 gap-y-5 md:grid lg:grid-cols-5">
                    {cloudProviders.map((p) => (
                        <div key={p.name} className="flex flex-col items-center gap-2">
                            <div className={`flex size-12 items-center justify-center rounded-xl lg:size-14 ${p.bg} ${p.color}`}>{p.icon}</div>
                            <span className="text-xs font-medium text-[#cbc3d7]/70">{p.name}</span>
                        </div>
                    ))}
                </div>
                <p className="mt-4 text-[11px] font-medium text-[#cbc3d7]/45 md:hidden">Swipe to explore</p>
            </section>

            {/* Features */}
            <section className="mx-auto max-w-5xl px-4 pb-20 sm:px-6 sm:pb-24">
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {features.map((f) => (
                        <div key={f.title} className="rounded-xl bg-[#151b2d] p-5 transition-colors hover:bg-[#1a2139] sm:p-6">
                            <div className="mb-3 inline-flex size-12 items-center justify-center rounded-xl bg-[#d0bcff]/10 text-[#d0bcff] sm:mb-4 sm:size-14">
                                {f.icon}
                            </div>
                            <h3 className="font-headline mb-2 text-base font-bold">{f.title}</h3>
                            <p className="text-sm leading-relaxed text-[#cbc3d7]">{f.desc}</p>
                        </div>
                    ))}
                </div>
            </section>

            {/* Footer */}
            <footer className="border-t border-[#494454]/20 px-4 py-10 text-center text-xs leading-relaxed text-[#cbc3d7]/40 sm:px-6">
                <span className="font-headline font-bold text-[#d0bcff]/40">Kuven</span>
                <span className="mx-2">·</span>
                Your Code. Any Cloud. We Handle the Rest.
            </footer>
        </div>
    );
}
