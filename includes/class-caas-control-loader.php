<?php
/**
 * Registers actions and filters with WordPress.
 *
 * @package Caas_Control
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Collects hooks and registers them in bulk.
 */
class Caas_Control_Loader {

	/**
	 * Actions to register.
	 *
	 * @var array<int, array{hook: string, component: object, callback: string, priority: int, accepted_args: int}>
	 */
	protected array $actions = array();

	/**
	 * Filters to register.
	 *
	 * @var array<int, array{hook: string, component: object, callback: string, priority: int, accepted_args: int}>
	 */
	protected array $filters = array();

	/**
	 * Add an action hook.
	 *
	 * @param string               $hook          Hook name.
	 * @param object               $component     Instance owning the callback.
	 * @param string               $callback      Method name.
	 * @param int                  $priority      Priority.
	 * @param int                  $accepted_args Accepted argument count.
	 */
	public function add_action( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Add a filter hook.
	 *
	 * @param string               $hook          Hook name.
	 * @param object               $component     Instance owning the callback.
	 * @param string               $callback      Method name.
	 * @param int                  $priority      Priority.
	 * @param int                  $accepted_args Accepted argument count.
	 */
	public function add_filter( string $hook, object $component, string $callback, int $priority = 10, int $accepted_args = 1 ): void {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Append a hook definition.
	 *
	 * @param array<int, array{hook: string, component: object, callback: string, priority: int, accepted_args: int}> $hooks         Collection.
	 * @param string                                                                                                  $hook          Hook name.
	 * @param object                                                                                                  $component     Instance.
	 * @param string                                                                                                  $callback      Method.
	 * @param int                                                                                                     $priority      Priority.
	 * @param int                                                                                                     $accepted_args Args.
	 * @return array<int, array{hook: string, component: object, callback: string, priority: int, accepted_args: int}>
	 */
	private function add( array $hooks, string $hook, object $component, string $callback, int $priority, int $accepted_args ): array {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
		return $hooks;
	}

	/**
	 * Register all collected hooks.
	 */
	public function run(): void {
		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}

		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['accepted_args']
			);
		}
	}
}
