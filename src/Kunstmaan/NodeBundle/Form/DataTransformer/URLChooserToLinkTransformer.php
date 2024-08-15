<?php

namespace Kunstmaan\NodeBundle\Form\DataTransformer;

use Kunstmaan\NodeBundle\Form\Type\URLChooserType;
use Kunstmaan\NodeBundle\Validation\URLValidator;
use Symfony\Component\Form\DataTransformerInterface;

class URLChooserToLinkTransformer implements DataTransformerInterface
{
    use URLValidator;

    public function transform($value): array
    {
        if ($value === null) {
            return [
                'link_type' => URLChooserType::INTERNAL,
                'link_url' => $value,
            ];
        }

        $data = [];
        if ($this->isEmailAddress($value)) {
            $data['choice_email'] = $value;
            $linkType = URLChooserType::EMAIL;
        } elseif ($this->isInternalLink($value) || $this->isInternalMediaLink($value)) {
            $data['choice_interal']['input'] = $value;
            $linkType = URLChooserType::INTERNAL;
        } else {
            $data['choice_external'] = $value;
            $linkType = URLChooserType::EXTERNAL;
        }

        return array_merge($data, [
            'link_type' => $linkType,
            'link_url' => $value,
        ]);
    }

    public function reverseTransform($value): ?string
    {
        if (!empty($value['link_type'])) {
            switch ($value['link_type']) {
                case URLChooserType::INTERNAL:
                    return $value['link_url'];
                case URLChooserType::EXTERNAL:
                    return $value['choice_external'];
                case URLChooserType::EMAIL:
                    return $value['choice_email'];
            }
        }

        return $value['link_url'];
    }
}
