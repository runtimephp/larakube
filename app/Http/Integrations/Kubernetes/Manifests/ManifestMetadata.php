<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

final readonly class ManifestMetadata
{
    public function __construct(
        public string $name,
        public ?string $namespace = null,
        public LabelSet $labels = new LabelSet(),
        public AnnotationSet $annotations = new AnnotationSet(),
    ) {}

    /**
     * @return array{name: string, namespace?: string, labels?: array<string, string>, annotations?: array<string, string>}
     */
    public function toArray(): array
    {
        $metadata = [
            'name' => $this->name,
        ];

        if ($this->namespace !== null) {
            $metadata['namespace'] = $this->namespace;
        }

        $labels = $this->labels->toArray();
        if ($labels !== []) {
            $metadata['labels'] = $labels;
        }

        $annotations = $this->annotations->toArray();
        if ($annotations !== []) {
            $metadata['annotations'] = $annotations;
        }

        return $metadata;
    }
}
