import { AwsLogo } from '@/components/icons/aws-logo';
import { ValkeyLogo } from '@/components/icons/valkey-logo';
import { SiAkamai, SiDjango, SiDigitalocean, SiExpress, SiFastapi, SiGo, SiHetzner, SiKubernetes, SiLaravel, SiNestjs, SiNextdotjs, SiNuxt, SiRubyonrails, SiSpringboot, SiVultr } from '@icons-pack/react-simple-icons';
import { Head, useForm, usePage } from '@inertiajs/react';
import { AppWindow, Bolt, Cloud, Database, Globe, HardDrive, Loader2, Radio, Server, ShieldCheck } from 'lucide-react';
import { type FormEventHandler } from 'react';

const applications = [
    { icon: <AppWindow className="size-4" />, name: 'web-app', meta: 'Laravel • 3 pods', status: 'Running', badgeClass: 'bg-emerald-500/10 text-emerald-400' },
    { icon: <Bolt className="size-4" />, name: 'scheduler', meta: 'Laravel • 1 pod', status: 'Running', badgeClass: 'bg-emerald-500/10 text-emerald-400' },
    { icon: <Server className="size-4" />, name: 'queue-worker', meta: 'Laravel • 2 pods', status: 'Running', badgeClass: 'bg-emerald-500/10 text-emerald-400' },
    { icon: <Radio className="size-4" />, name: 'websockets', meta: 'Reverb • 1 pod', status: 'Running', badgeClass: 'bg-emerald-500/10 text-emerald-400' },
];

const resources = [
    { icon: <Database className="size-4" />, name: 'postgres-18', meta: 'Database • eu-central', status: 'Provisioned', badgeClass: 'bg-[#d0bcff]/10 text-[#d0bcff]' },
    { icon: <ValkeyLogo className="size-4" />, name: 'valkey', meta: 'Cache • eu-central', status: 'Provisioned', badgeClass: 'bg-[#d0bcff]/10 text-[#d0bcff]' },
    { icon: <HardDrive className="size-4" />, name: 'object-storage', meta: 'S3 • eu-central', status: 'Provisioned', badgeClass: 'bg-[#d0bcff]/10 text-[#d0bcff]' },
    { icon: <Radio className="size-4" />, name: 'redis-queue', meta: 'Queue backend', status: 'Provisioned', badgeClass: 'bg-[#d0bcff]/10 text-[#d0bcff]' },
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

export default function Welcome() {
    const { waitlistCount } = usePage<{ waitlistCount: number }>().props;
    const flash = usePage().props.flash as Record<string, boolean> | undefined;
    const joined = flash?.waitlist_success;

    const { data, setData, post, processing, errors } = useForm({ email: '' });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('waitlist.store'));
    };

    return (
        <div className="kuven-dark dark min-h-screen bg-[#0c1324] text-[#dce1fb]">
            <Head title="Your Code. Any Cloud. We Handle the Rest." />

            {/* Nav */}
            <nav className="mx-auto flex max-w-5xl items-center justify-between px-6 py-6">
                <span className="font-headline text-2xl font-black tracking-tighter text-[#d0bcff]">Kuven</span>
                <div className="flex items-center gap-2">
                    {providers.map((p, i) => (
                        <div key={i} className={`flex size-7 items-center justify-center rounded-md text-white ${p.bg}`}>
                            {p.icon}
                        </div>
                    ))}
                </div>
            </nav>

            {/* Hero */}
            <section className="mx-auto max-w-5xl px-6 pb-20 pt-16 text-center">
                <div className="mb-8 inline-flex items-center gap-2 rounded-full border border-[#d0bcff]/20 bg-[#d0bcff]/10 px-4 py-1.5 text-xs font-bold uppercase tracking-widest text-[#d0bcff]">
                    Early Access
                </div>

                <h1 className="font-headline text-4xl font-extrabold leading-[1.1] tracking-tight sm:text-6xl md:text-7xl">
                    Your Code. Any Cloud.
                    <br />
                    <span className="text-[#d0bcff]">We Handle the Rest.</span>
                </h1>

                <p className="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-[#cbc3d7]">
                    Scale Laravel, Next.js, Nuxt, Django, FastAPI, and Go on K8s with zero YAML. Kuven abstracts the complexity into a unified control plane for high-velocity engineering.
                </p>

                {/* Waitlist form */}
                <div className="mx-auto mt-10 max-w-md">
                    {joined ? (
                        <div className="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-6 py-4 text-sm text-emerald-400">
                            You're on the list. We'll be in touch.
                        </div>
                    ) : (
                        <form onSubmit={submit} className="flex gap-2">
                            <input
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="you@company.com"
                                required
                                className="flex-1 rounded-lg border border-[#494454] bg-[#151b2d] px-4 py-3 text-sm text-[#dce1fb] placeholder:text-[#6b6b80] focus:border-[#d0bcff]/30 focus:outline-none focus:ring-[3px] focus:ring-[#d0bcff]/25"
                            />
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center gap-2 rounded-lg bg-gradient-to-br from-[#d0bcff] to-[#a078ff] px-6 py-3 text-sm font-semibold text-[#3c0091] transition-all active:scale-95 disabled:opacity-50"
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
            <section className="mx-auto max-w-5xl px-6 pb-24">
                <div className="overflow-hidden rounded-xl border border-[#494454]/30 bg-[#151b2d] shadow-2xl shadow-[#d0bcff]/5">
                    {/* Window chrome */}
                    <div className="flex items-center gap-4 border-b border-[#494454]/20 px-5 py-3">
                        <div className="flex gap-1.5">
                            <div className="size-2.5 rounded-full bg-[#ffb4ab]/40" />
                            <div className="size-2.5 rounded-full bg-[#ffb869]/40" />
                            <div className="size-2.5 rounded-full bg-[#d0bcff]/40" />
                        </div>
                        <span className="text-[10px] font-medium uppercase tracking-widest text-[#cbc3d7]/40">
                            Service Catalog / Production Cluster
                        </span>
                    </div>

                    {/* Two-column layout: Applications ←→ Resources */}
                    <div className="grid grid-cols-[1fr_auto_1fr] items-start gap-0 p-6">
                        {/* Applications */}
                        <div>
                            <p className="mb-3 text-[10px] font-bold uppercase tracking-widest text-[#cbc3d7]/40">Applications</p>
                            <div className="space-y-2">
                                {applications.map((s) => (
                                    <div key={s.name} className="flex items-center gap-3 rounded-lg bg-[#0c1324] p-3 transition-colors hover:bg-[#1a2139]">
                                        <div className="flex size-8 shrink-0 items-center justify-center rounded bg-emerald-500/10 text-emerald-400">
                                            {s.icon}
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <h3 className="font-headline text-sm font-bold text-[#dce1fb]">{s.name}</h3>
                                            <p className="text-[10px] text-[#cbc3d7]/60">{s.meta}</p>
                                        </div>
                                        <span className={`shrink-0 rounded px-2 py-0.5 text-[9px] font-bold uppercase tracking-tighter ${s.badgeClass}`}>
                                            {s.status}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>

                        {/* Connection dots */}
                        <div className="flex flex-col items-center justify-center self-stretch px-6 py-10">
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
                            <p className="mb-3 text-[10px] font-bold uppercase tracking-widest text-[#cbc3d7]/40">Resources</p>
                            <div className="space-y-2">
                                {resources.map((s) => (
                                    <div key={s.name} className="flex items-center gap-3 rounded-lg bg-[#0c1324] p-3 transition-colors hover:bg-[#1a2139]">
                                        <div className="flex size-8 shrink-0 items-center justify-center rounded bg-[#d0bcff]/10 text-[#d0bcff]">
                                            {s.icon}
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <h3 className="font-headline text-sm font-bold text-[#dce1fb]">{s.name}</h3>
                                            <p className="text-[10px] text-[#cbc3d7]/60">{s.meta}</p>
                                        </div>
                                        <span className={`shrink-0 rounded px-2 py-0.5 text-[9px] font-bold uppercase tracking-tighter ${s.badgeClass}`}>
                                            {s.status}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {/* Supported Stacks */}
            <section className="mx-auto max-w-5xl px-6 pb-24 text-center">
                <p className="mb-8 text-xs font-bold uppercase tracking-widest text-[#d0bcff]/60">Built for your stack</p>
                <div className="grid grid-cols-5 gap-4 max-w-2xl mx-auto">
                    {[
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
                    ].map((s) => (
                        <div key={s.name} className="flex flex-col items-center gap-2">
                            <div className={`flex size-14 items-center justify-center rounded-xl ${s.bg} ${s.color}`}>{s.icon}</div>
                            <span className="text-[11px] font-medium text-[#cbc3d7]/70">{s.name}</span>
                        </div>
                    ))}
                </div>
            </section>

            {/* Bring Your Own Cloud */}
            <section className="mx-auto max-w-5xl px-6 pb-24 text-center">
                <p className="mb-8 text-xs font-bold uppercase tracking-widest text-[#d0bcff]/60">Bring your own cloud</p>
                <div className="grid grid-cols-5 gap-4 max-w-2xl mx-auto">
                    {[
                        { icon: <SiHetzner className="size-7" />, name: 'Hetzner', bg: 'bg-[#d50c2d]', color: 'text-white' },
                        { icon: <SiDigitalocean className="size-7" />, name: 'DigitalOcean', bg: 'bg-[#0080ff]', color: 'text-white' },
                        { icon: <AwsLogo className="size-7" />, name: 'AWS', bg: 'bg-[#232f3e]', color: 'text-[#ff9900]' },
                        { icon: <SiVultr className="size-7" />, name: 'Vultr', bg: 'bg-[#007bfc]', color: 'text-white' },
                        { icon: <SiAkamai className="size-7" />, name: 'Akamai', bg: 'bg-[#0096d6]', color: 'text-white' },
                    ].map((p) => (
                        <div key={p.name} className="flex flex-col items-center gap-2">
                            <div className={`flex size-14 items-center justify-center rounded-xl ${p.bg} ${p.color}`}>{p.icon}</div>
                            <span className="text-[11px] font-medium text-[#cbc3d7]/70">{p.name}</span>
                        </div>
                    ))}
                </div>
            </section>

            {/* Features */}
            <section className="mx-auto max-w-5xl px-6 pb-24">
                <div className="grid gap-6 sm:grid-cols-3">
                    {features.map((f) => (
                        <div key={f.title} className="rounded-xl bg-[#151b2d] p-6 transition-colors hover:bg-[#1a2139]">
                            <div className="mb-4 inline-flex size-14 items-center justify-center rounded-xl bg-[#d0bcff]/10 text-[#d0bcff]">
                                {f.icon}
                            </div>
                            <h3 className="font-headline text-base font-bold mb-2">{f.title}</h3>
                            <p className="text-sm leading-relaxed text-[#cbc3d7]">{f.desc}</p>
                        </div>
                    ))}
                </div>
            </section>

            {/* Footer */}
            <footer className="border-t border-[#494454]/20 py-8 text-center text-xs text-[#cbc3d7]/40">
                <span className="font-headline font-bold text-[#d0bcff]/40">Kuven</span>
                <span className="mx-2">·</span>
                Your Code. Any Cloud. We Handle the Rest.
            </footer>
        </div>
    );
}
