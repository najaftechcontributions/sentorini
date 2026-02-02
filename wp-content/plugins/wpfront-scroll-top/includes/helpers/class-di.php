<?php
/**
 * WPFront Scroll Top
 *
 * @package     wpfront-scroll-top
 * @author      Syam Mohan
 * @copyright   2013 WPFront
 * @license     GPL-2.0-or-later
 */

namespace WPFront\Scroll_Top;

defined( 'ABSPATH' ) || exit;

use Exception;

/**
 * Dependency Injection Container
 */
class DI {

	/**
	 * The instances of the container.
	 *
	 * @var array<class-string, object>
	 */
	protected static $bindings = array();

	/**
	 * Returns the normalized/parent class name of the given class.
	 *
	 * @param class-string $id The class name to normalize.
	 * @return class-string
	 */
	private function normalize_id( string $id ): string {
		$reflection_class = new \ReflectionClass( $id );

		while ( false !== $reflection_class ) {
			$id               = $reflection_class->getName();
			$reflection_class = $reflection_class->getParentClass();
		}

		return $id;
	}

	/**
	 * Returns the instance of the given class.
	 *
	 * @template T
	 * @param class-string<T> $id The class name to resolve.
	 * @return T
	 */
	public function get( $id ) {
		self::$bindings[ self::class ] = $this;

		$obj = $this->has( $id );

		if ( null !== $obj ) {
			return $obj;
		}

		// @phpstan-ignore-next-line
		$obj = $this->build( $id );
		$this->set( $id, $obj );

		// @phpstan-ignore return.type
		return $obj;
	}

	/**
	 * Returns the instance of the given class if it exists.
	 *
	 * @param class-string<T> $id The class name to resolve.
	 * @template T
	 * @return T|null
	 */
	private function has( $id ) {
		$id = $this->normalize_id( $id );
		if ( isset( self::$bindings[ $id ] ) ) {
			/**
			 * Object of type T
			 *
			 * @var T
			 */
			return self::$bindings[ $id ];
		}

		return null;
	}

	/**
	 * Sets the instance of the given class.
	 *
	 * @param class-string<T> $id The class name to resolve.
	 * @param object|T        $obj The object to set.
	 * @template T
	 * @return void
	 */
	private function set( $id, $obj ) {
		$id = $this->normalize_id( $id );
		// @phpstan-ignore assign.propertyType
		self::$bindings[ $id ] = $obj;
	}

	/**
	 * Builds the object of the given class.
	 *
	 * @param class-string<T> $id The class name to resolve.
	 * @template T of object
	 * @throws Exception Exception if the class is not instantiable or if the constructor parameters are not type-hinted.
	 * @return T
	 */
	private function build( $id ) {

		$reflection_class = new \ReflectionClass( $id );

		if ( ! $reflection_class->isInstantiable() ) {
			throw new Exception( sprintf( 'Class %s is not instantiable.', esc_html( $id ) ) );
		}

		$constructor = $reflection_class->getConstructor();

		if ( ! $constructor ) {
			return $reflection_class->newInstance();
		}

		$params = $constructor->getParameters();

		if ( ! $params ) {
			return $reflection_class->newInstance();
		}

		$dependencies = array();

		foreach ( $params as $param ) {
			$param_name = $param->getName();
			$param_type = $param->getType();

			if ( ! $param_type ) {
				throw new Exception( sprintf( 'Parameter is not type-hinted.' ) );
			}

			if ( $param_type instanceof \ReflectionNamedType && ! $param_type->isBuiltin() ) {
				// @phpstan-ignore-next-line
				array_push( $dependencies, $this->get( $param_type->getName() ) );
				continue;
			}

			throw new Exception( sprintf( 'Parameter is not a class type.' ) );
		}

		return $reflection_class->newInstanceArgs( $dependencies );
	}
}
