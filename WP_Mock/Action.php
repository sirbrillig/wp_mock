<?php
/**
 * Mock WordPress actions by substituting each action with an advanced object
 * capable of intercepting calls and returning predictable behavior.
 *
 * @package WP_Mock
 * @subpackage Hooks
 */

namespace WP_Mock;


class Action extends Hook {
	public function react( $args ) {
		\WP_Mock::invokeAction( $this->name );

		$arg_num = count( $args );

		if ( 0 === $arg_num ) {
			if ( ! isset( $this->processors['argsnull'] ) ) {
				return;
			}

			$this->processors['argsnull']->react();
		} else {
			$processors = $this->processors;
			for( $i = 0; $i < $arg_num - 1; $i++ ) {
				$arg = $this->safe_offset( $args[ $i ] );

				if ( ! isset( $processors[ $arg ] ) ) {
					continue;
				}

				$processors = $processors[ $arg ];
			}

			$offset = $this->safe_offset( $args[ $arg_num - 1 ] );
			$reactor = isset( $processors[ $offset ] ) ? $processors[ $offset ] : array_shift( $processors );
			if ( isset( $reactor ) ) {
				$reactor->react();
			}
		}
	}

	protected function new_responder() {
		return new Action_Responder();
	}
}

class Action_Responder {
	/**
	 * @var mixed
	 */
	protected $callable;

	public function perform( $callable ) {
		$this->callable = $callable;
	}

	public function react() {
		call_user_func( $this->callable );
	}
}
