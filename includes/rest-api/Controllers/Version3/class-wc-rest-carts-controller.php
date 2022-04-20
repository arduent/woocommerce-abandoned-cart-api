<?php
/**
 * REST API Carts controller
 *
 * Handles requests to the /carts endpoint.
 *
 * @author   Waitman Gobble
 * @category API
 * @package WooCommerce\RestApi
 * @since    6.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

/**
 * REST API Carts controller class.
 *
 * @package WooCommerce\RestApi
 * @extends WC_REST_Controller
 */
class WC_REST_Carts_Controller extends WC_REST_Controller {

        /**
         * Endpoint namespace.
         *
         * @var string
         */
        protected $namespace = 'wc/v3';

        /**
         * Route base.
         *
         * @var string
         */
        protected $rest_base = 'carts';

        public function register_routes() {
                register_rest_route( $this->namespace, '/' . $this->rest_base, array(
                        array(
                                'methods'             => WP_REST_Server::READABLE,
                                'callback'            => array( $this, 'get_items' ),
                                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                                'args'                => $this->get_collection_params(),
                        ),
                        'schema' => array( $this, 'get_public_item_schema' ),
                ) );
		}

		
        /**
         * Check whether a given request has permission to read carts.
         *
         * @param  WP_REST_Request $request Full details about the request.
         * @return WP_Error|boolean
         */
        public function get_items_permissions_check( $request ) {
                if ( ! wc_rest_check_user_permissions( 'read' ) ) {
                        return new WP_Error( 'woocommerce_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
                }

                return true;
        }

        /**
         * Prepare a single item for response. Handles setting the status based on the payment result.
         *
         * @param mixed            $item Item to format to schema.
         * @param \WP_REST_Request $request Request object.
         * @return \WP_REST_Response $response Response data.
         */
        public function prepare_item_for_response( $item, $response ) {

                return $item;
        }

	public function prepare_response_for_collection($data)
	{
		return $data;
	}


        /**
         * Get all carts.
         *
         * @param WP_REST_Request $request Full details about the request.
         * @return WP_Error|WP_REST_Response
         */
        public function get_items( $request ) {

				global $wpdb;

				$active_carts = [];
				
				$carts = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_sessions ORDER BY session_expiry ASC") ); // @codingStandardsIgnoreLine.
				
                foreach ( $carts as $cart) {
                        $data = $this->prepare_item_for_response( $cart, $request );
                        $active_carts[] = $this->prepare_response_for_collection( $data );
                }

                
                $response = rest_ensure_response( $active_carts );
                return $response;
		}
}
