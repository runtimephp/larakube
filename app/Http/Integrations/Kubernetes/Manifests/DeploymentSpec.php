<?php

declare(strict_types=1);

namespace App\Http\Integrations\Kubernetes\Manifests;

use InvalidArgumentException;

final readonly class DeploymentSpec
{
    public function __construct(
        public int $replicas,
        public LabelSelector $selector,
        public PodTemplateSpec $template,
    ) {
        $selectorLabels = $this->selector->matchLabels->toArray();
        $templateLabels = $this->template->metadata->labels->toArray();

        foreach ($selectorLabels as $key => $value) {
            if (! array_key_exists($key, $templateLabels) || $templateLabels[$key] !== $value) {
                throw new InvalidArgumentException('Deployment selector labels must match template metadata labels.');
            }
        }
    }

    /**
     * @return array{replicas: int, selector: array{matchLabels: array<string, string>}, template: array{metadata: array{name: string, namespace?: string, labels?: array<string, string>, annotations?: array<string, string>}, spec: array{containers: list<array{name: string, image: string, ports?: list<array{containerPort: int, protocol: string, name?: string}>, env?: list<array{name: string, value: string}>}>, serviceAccountName?: string}}}
     */
    public function toArray(): array
    {
        return [
            'replicas' => $this->replicas,
            'selector' => $this->selector->toArray(),
            'template' => $this->template->toArray(),
        ];
    }
}
