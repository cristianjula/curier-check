<?php

class CurieRO_PFPJ_Hooks_Loader
{
    protected $actions;

    protected $filters;

    public function __construct()
    {
        $this->actions = [];
        $this->filters = [];
    }

    public function add_action($hook, $component, $callback, $priority = 10, $accepted_args = 1): void
    {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
    }

    public function add_filter($hook, $component, $callback, $priority = 10, $accepted_args = 1): void
    {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
    }

    public function run(): void
    {
        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], [$hook['component'], $hook['callback']], $hook['priority'], $hook['accepted_args']);
        }

        foreach ($this->actions as $hook) {
            add_action($hook['hook'], [$hook['component'], $hook['callback']], $hook['priority'], $hook['accepted_args']);
        }
    }

    private function add($hooks, $hook, $component, $callback, $priority, $accepted_args): array
    {
        $hooks[] = [
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        ];

        return $hooks;
    }
}

new CurieRO_PFPJ_Hooks_Loader();
