<?php
/**
 * Access Control List cache class.
 *
 * Implements caching for Access Control List.
 *
 * This class uses a custom namespace supporting File caching class
 * called NamespaceFileEngine. This engine can be found here:
 * https://github.com/Phally/cache_engines
 *
 * The settings for this cache should be
 * defined at the bottom of /config/bootstrap.php like the following:
 *
 * App::import('Vendor', 'CacheEngines.NamespaceFile');
 * Cache::config('acl', array('engine' => 'NamespaceFile', 'duration'=> '+1 month', 'prefix' => 'acl.'));
 *
 * @author Frank de Graaf (Phally)
 * @link http://www.frankdegraaf.net
 * @license MIT license
 */
App::import('Component', 'Acl');
class CachedAclComponent extends AclComponent {

/**
 * Initialize method to assure ACL methods remain working as
 * they are described in the book.
 *
 * @param object $controller The current controller.
 * @access public
 */
	function initialize(&$controller) {
		$controller->Acl =& $this;
	}

/**
 * Check method.
 *
 * This method overrides and uses the original
 * method. It only adds cache to it.
 *
 * @param string $aro ARO
 * @param string $aco ACO
 * @param string $action Action (defaults to *)
 * @access public
 */
	function check($aro, $aco, $action = "*") {
		$path = $this->__cachePath($aro, $aco, $action);
		$check = Cache::read($path, 'acl');
		if ($check === false) {
			$check = parent::check($aro, $aco, $action);
			Cache::write($path, $check ? true : 0, 'acl');
		} else {
			$check = $check === true;
		}
		return $check;
	}

/**
 * Allow method.
 *
 * This method overrides and uses the original
 * method. It only adds cache to it.
 *
 * @param string $aro ARO
 * @param string $aco ACO
 * @param string $action Action (defaults to *)
 * @access public
 */
	function allow($aro, $aco, $action = "*") {
		parent::allow($aro, $aco, $action);
		$this->__deleteCache($aro, $aco, $action);
	}

/**
 * Deny method.
 *
 * This method overrides and uses the original
 * method. It only adds cache to it.
 *
 * @param string $aro ARO
 * @param string $aco ACO
 * @param string $action Action (defaults to *)
 * @access public
 */
	function deny($aro, $aco, $action = "*") {
		parent::deny($aro, $aco, $action);
		$this->__deleteCache($aro, $aco, $action);
	}

/**
 * Inherit method.
 *
 * This method overrides and uses the original
 * method. It only adds cache to it.
 *
 * @param string $aro ARO
 * @param string $aco ACO
 * @param string $action Action (defaults to *)
 * @access public
 */
	function inherit($aro, $aco, $action = "*") {
		parent::inherit($aro, $aco, $action);
		$this->__deleteCache($aro, $aco, $action);
	}

/**
 * Grant method.
 *
 * This method overrides and uses the original
 * method. It only adds cache to it.
 *
 * @param string $aro ARO
 * @param string $aco ACO
 * @param string $action Action (defaults to *)
 * @access public
 */
	function grant($aro, $aco, $action = "*") {
		parent::grant($aro, $aco, $action);
		$this->__deleteCache($aro, $aco, $action);
	}

/**
 * Revoke method.
 *
 * This method overrides and uses the original
 * method. It only adds cache to it.
 *
 * @param string $aro ARO
 * @param string $aco ACO
 * @param string $action Action (defaults to *)
 * @access public
 */
	function revoke($aro, $aco, $action = "*") {
		parent::revoke($aro, $aco, $action);
		$this->__deleteCache($aro, $aco, $action);
	}

/**
 * Returns a dot separated path to use as the cache key.
 *
 * @param string $aro ARO
 * @param string $aco ACO
 * @param boolean $acoPath Boolean to return only the path to the ACO or the full path to the permission.
 * @access private
 */
	function __cachePath($aro, $aco, $action, $acoPath = false) {
		if ($action != "*") {
			$aco .= '/' . $action;
		}
		$path = Inflector::slug($aco);

		if (!$acoPath) {
			if (!is_array($aro)) {
				$_aro = explode(':', $aro);
			} elseif (Set::countDim($aro) > 1) {
				$_aro = array(key($aro), current(current($aro)));
			} else {
				$_aro = array_values($aro);
			}
			$path .= '.' . Inflector::slug(implode('.', $_aro));
		}

		return strtolower($path);
	}

/**
 * Deletes an ACO from the cache.
 *
 * @param string $aro ARO
 * @param string $aco ACO
 * @access private
 */
	function __deleteCache($aro, $aco, $action) {
		Cache::delete($this->__cachePath($aro, $aco, $action, true), 'acl');
	}
}
?>