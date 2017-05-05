<?php
/**
 * Created by PhpStorm.
 * User: david
 * Date: 17-4-7
 * Time: 下午1:11
 */

namespace common\workflow;


class PostWorkflow implements \raoul2000\workflow\source\file\IWorkflowDefinitionProvider
{
    public function getDefinition() {
        return [
            'initialStatusId' => 'draft',
            'status' => [
                'draft' => [
                    'transition' => ['correction']
                ],
                'correction' => [
                    'transition' => ['draft','ready']
                ],
                'ready' => [
                    'transition' => ['draft', 'correction', 'published']
                ],
                'published' => [
                    'transition' => ['ready', 'archived']
                ],
                'archived' => [
                    'transition' => ['ready']
                ]
            ]
        ];
    }
}