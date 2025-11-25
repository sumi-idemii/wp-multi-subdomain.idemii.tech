wp.domReady( () => {

	// 埋め込み
	wp.blocks.unregisterBlockVariation( 'core/embed', 'twitter' );       // Twitter
	// wp.blocks.unregisterBlockVariation( 'core/embed', 'youtube' );       // YouTube
	wp.blocks.unregisterBlockVariation( 'core/embed', 'wordpress' );     // WordPress
	wp.blocks.unregisterBlockVariation( 'core/embed', 'soundcloud' );    // SoundCloud
	wp.blocks.unregisterBlockVariation( 'core/embed', 'spotify' );       // Spotify
	wp.blocks.unregisterBlockVariation( 'core/embed', 'flickr' );        // Flickr
	wp.blocks.unregisterBlockVariation( 'core/embed', 'vimeo' );         // Vimeo
	wp.blocks.unregisterBlockVariation( 'core/embed', 'animoto' );       // Animoto
	wp.blocks.unregisterBlockVariation( 'core/embed', 'cloudup' );       // Cloudup
	wp.blocks.unregisterBlockVariation( 'core/embed', 'crowdsignal' );   // Crowdsignal
	wp.blocks.unregisterBlockVariation( 'core/embed', 'dailymotion' );   // Dailymotion
	wp.blocks.unregisterBlockVariation( 'core/embed', 'imgur' );         // Imgur
	wp.blocks.unregisterBlockVariation( 'core/embed', 'issuu' );         // Issuu
	wp.blocks.unregisterBlockVariation( 'core/embed', 'kickstarter' );   // Kickstarter
	wp.blocks.unregisterBlockVariation( 'core/embed', 'meetup-com' );    // Meetup.com
	wp.blocks.unregisterBlockVariation( 'core/embed', 'mixcloud' );      // Mixcloud
	wp.blocks.unregisterBlockVariation( 'core/embed', 'reddit' );        // Reddit
	wp.blocks.unregisterBlockVariation( 'core/embed', 'reverbnation' );  // ReverbNation
	wp.blocks.unregisterBlockVariation( 'core/embed', 'screencast' );    // Screencast
	wp.blocks.unregisterBlockVariation( 'core/embed', 'scribd' );        // Scribd
	wp.blocks.unregisterBlockVariation( 'core/embed', 'slideshare' );    // Slideshare
	wp.blocks.unregisterBlockVariation( 'core/embed', 'smugmug' );       // SmugMug
	wp.blocks.unregisterBlockVariation( 'core/embed', 'speaker-deck' );  // Speaker Deck
	wp.blocks.unregisterBlockVariation( 'core/embed', 'tiktok' );        // TikTok
	wp.blocks.unregisterBlockVariation( 'core/embed', 'ted' );           // TED
	wp.blocks.unregisterBlockVariation( 'core/embed', 'tumblr' );        // Tumblr
	wp.blocks.unregisterBlockVariation( 'core/embed', 'videopress' );    // VideoPress
	wp.blocks.unregisterBlockVariation( 'core/embed', 'wordpress-tv' );  // WordPress.tv
	wp.blocks.unregisterBlockVariation( 'core/embed', 'amazon-kindle' ); // Amazon Kindle


	wp.blocks.unregisterBlockVariation( 'core/embed', 'pocket-casts' );
	wp.blocks.unregisterBlockVariation( 'core/embed', 'pinterest' );
	wp.blocks.unregisterBlockVariation( 'core/embed', 'wolfram-cloud' );
	wp.blocks.unregisterBlockVariation( 'core/embed', 'bluesky' );

	// https://github.com/WordPress/gutenberg/blob/trunk/packages/block-library/src/embed/variations.js
} );