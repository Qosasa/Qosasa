<?php namespace Qosasa\Core\Format;

use Qosasa\Core\Exceptions\FormatProviderResolverException;
use Qosasa\Core\Format\FormatInflater;
use Qosasa\Core\Format\Providers\JSONFormatProvider;
use Qosasa\Core\Snippet;


class FormatLoader {

    /**
     * Create a new format loader
     *
     * @param  \Qosasa\Core\Snippet  $snippet
     * @return void
     */
    public function __construct(Snippet $snippet, FormatInflater $formatInflater)
    {
        $this->snippet = $snippet;
        $this->formatInflater = $formatInflater;
    }

    /**
     * Load the format
     *
     * @return Format
     */
    public function load()
    {
        // Get info from snippet
        $file = $this->snippet->getFormatFile();
        $providerName = $this->snippet->getProvider();

        // Resolve provider
        $provider = $this->resolveFormatProvider($providerName, $file);

        // Get format from provider
        $format = $provider->getFormat();

        // Inflate format
        $inflatedFormat = $this->formatInflater->inflate($format);

        return $inflatedFormat;
    }

    /**
     * Return provider
     *
     * @param  string  $providerName
     * @param  \League\Flysystem\File  $file
     * @return FormatProviderInterface
     */
    public function resolveFormatProvider($providerName, $file)
    {
        switch ($providerName) {
            case 'json':
                return new JSONFormatProvider($file);
        }
        throw new FormatProviderResolverException("Resolver couldn't find a provider for $providerName");
    }

}
