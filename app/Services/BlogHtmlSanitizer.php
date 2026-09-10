<?php

namespace App\Services;

class BlogHtmlSanitizer
{
    public function clean(string $html): string
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null);
        $config->set('HTML.Allowed', 'h1[style],h2[style],h3[style],h4[style],h5[style],h6[style],p[style],div[style],span[style],br,strong,b,em,i,u,s,sub,sup,blockquote,pre,code,ul,ol[start],li,a[href|title|target|rel],img[src|alt|title|width|height|style],table[style|width|border|cellpadding|cellspacing],caption,thead,tbody,tfoot,tr,th[colspan|rowspan|scope|style],td[colspan|rowspan|style],hr');
        $config->set('CSS.AllowedProperties', ['text-align', 'font-size', 'font-weight', 'font-style', 'text-decoration', 'color', 'background-color', 'width', 'height', 'max-width', 'margin-left', 'margin-right', 'padding', 'border', 'border-collapse', 'vertical-align']);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.Nofollow', false);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        return (new \HTMLPurifier($config))->purify($html);
    }
}
