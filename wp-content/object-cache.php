<?php

/**
 * Description: XCache backend for the WordPress Object Cache.
 * Version: 1.0
 * Author: Pierre Schmitz
 * Author URI: https://pierre-schmitz.com/
*/

function wp_cache_add($key, $data, $group = '', $expire = 0) {
	global $wp_object_cache;

	return $wp_object_cache->add($key, $data, $group, $expire);
}

function wp_cache_close() {
	return true;
}

function wp_cache_decr( $key, $offset = 1, $group = '' ) {
	global $wp_object_cache;

	return $wp_object_cache->decr( $key, $offset, $group );
}

function wp_cache_delete($key, $group = '') {
	global $wp_object_cache;

	return $wp_object_cache->delete($key, $group);
}

function wp_cache_flush() {
	global $wp_object_cache;

	return $wp_object_cache->flush();
}

function wp_cache_get( $key, $group = '', $force = false ) {
	global $wp_object_cache;

	return $wp_object_cache->get( $key, $group, $force );
}

function wp_cache_incr( $key, $offset = 1, $group = '' ) {
	global $wp_object_cache;

	return $wp_object_cache->incr( $key, $offset, $group );
}


function wp_cache_init() {
	$GLOBALS['wp_object_cache'] = new XCache_Object_Cache();
}

function wp_cache_replace($key, $data, $group = '', $expire = 0) {
	global $wp_object_cache;

	return $wp_object_cache->replace($key, $data, $group, $expire);
}

function wp_cache_set($key, $data, $group = '', $expire = 0) {
	global $wp_object_cache;

	return $wp_object_cache->set($key, $data, $group, $expire);
}

function wp_cache_add_global_groups( $groups ) {
	global $wp_object_cache;

	return $wp_object_cache->add_global_groups($groups);
}

function wp_cache_add_non_persistent_groups( $groups ) {
	global $wp_object_cache;

	return $wp_object_cache->wp_cache_add_non_persistent_groups($groups);
}

function wp_cache_reset() {
	global $wp_object_cache;

	return $wp_object_cache->reset();
}

class XCache_Object_Cache {

	private $prefix = '';
	private $local_cache = array();
	private $global_groups = array();
	private $non_persistent_groups = array();

	public function __construct() {
		global $table_prefix;

		if ( !function_exists( 'xcache_get' ) ) {
			wp_die( 'You do not have XCache installed, so you cannot use the XCache object cache backend. Please remove the <code>object-cache.php</code> file from your content directory.' );
		}

		$this->prefix = DB_HOST.'.'.DB_NAME.'.'.$table_prefix;
	}


	private function get_key($group, $key) {
		if (empty($group)) {
			$group = 'default';
		}
		return $this->prefix.'.'.$group.'.'.$key;
	}

	public function add( $key, $data, $group = 'default', $expire = '' ) {
		if (wp_suspend_cache_addition()) {
			return false;
		}

		if (in_array($group, $this->non_persistent_groups)) {
			if (isset($this->local_cache[$group][$key])) {
				return false;
			}
		} elseif (xcache_isset($this->get_key($group, $key))) {
			return false;
		}

		return $this->set($key, $data, $group, $expire);
	}

	public function add_global_groups( $groups ) {
		$groups = (array) $groups;

		$this->global_groups = array_merge($this->global_groups, $groups);
		$this->global_groups = array_unique($this->global_groups);
	}

	public function wp_cache_add_non_persistent_groups( $groups ) {
		$groups = (array) $groups;

		$this->non_persistent_groups = array_merge($this->non_persistent_groups, $groups);
		$this->non_persistent_groups = array_unique($this->non_persistent_groups);
	}

	public function decr( $key, $offset = 1, $group = 'default' ) {
		if (in_array($group, $this->non_persistent_groups)) {
			if (isset($this->local_cache[$group][$key]) && $this->local_cache[$group][$key] - $offset >= 0) {
				$this->local_cache[$group][$key] -= $offset;
			} else {
				$this->local_cache[$group][$key] = 0;
			}
			return $this->local_cache[$group][$key];
		} else {
			return xcache_dec($this->get_key($group, $key), $offset);
		}
	}

	public function delete($key, $group = 'default', $force = false) {
		if (in_array($group, $this->non_persistent_groups)) {
			unset($this->local_cache[$group][$key]);
			return true;
		} else {
			return xcache_unset($this->get_key($group, $key));
		}
	}

	public function flush() {
		$this->local_cache = array ();
		return xcache_unset_by_prefix($this->prefix);
	}

	public function get( $key, $group = 'default', $force = false) {
		if (in_array($group, $this->non_persistent_groups)) {
			if (isset($this->local_cache[$group][$key])) {
				if (is_object($this->local_cache[$group][$key])) {
					return clone $this->local_cache[$group][$key];
				} else {
					return $this->local_cache[$group][$key];
				}
			} else {
				return false;
			}
		} else {
			return unserialize(xcache_get($this->get_key($group, $key)));
		}
	}

	public function incr( $key, $offset = 1, $group = 'default' ) {
		if (in_array($group, $this->non_persistent_groups)) {
			if (isset($this->local_cache[$group][$key]) && $this->local_cache[$group][$key] + $offset >= 0) {
				$this->local_cache[$group][$key] += $offset;
			} else {
				$this->local_cache[$group][$key] = 0;
			}
			return $this->local_cache[$group][$key];
		} else {
			return xcache_inc($this->get_key($group, $key), $offset);
		}
	}

	public function replace($key, $data, $group = 'default', $expire = '') {
		if (in_array($group, $this->non_persistent_groups) && isset($this->local_cache[$group][$key])) {
			if (is_object($data)) {
				$this->local_cache[$group][$key] = clone $data;
			} else {
				$this->local_cache[$group][$key] = $data;
			}
			return true;
		} else {
			return false;
		}
		if (xcache_isset($this->get_key($group, $key))) {
			return xcache_set($this->get_key($group, $key), serialize($data), $expire);
		} else {
			return false;
		}
	}

	public function reset() {
		// TODO: only remove non-global groups
		$this->flush();
	}

	public function set($key, $data, $group = 'default', $expire = '') {
		if (in_array($group, $this->non_persistent_groups)) {
			if (is_object($data)) {
				$this->local_cache[$group][$key] = clone $data;
			} else {
				$this->local_cache[$group][$key] = $data;
			}
			return true;
		} else {
			return xcache_set($this->get_key($group, $key), serialize($data), $expire);
		}
	}

	public function stats() {
		// TODO: print some stats
		echo '';
	}
}

?>
