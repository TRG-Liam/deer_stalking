/**
 * Front end behaviour for Blackthorn Hunting.
 *
 * Two small jobs: the header turns solid as the home page scrolls, and
 * content marked cds-fade eases in as it enters the viewport. No
 * dependencies.
 */
( function () {
	'use strict';

	var root = document.documentElement;

	if ( root.classList.contains( 'no-js' ) ) {
		root.classList.replace( 'no-js', 'js' );
	} else {
		root.classList.add( 'js' );
	}

	/* Header: transparent over the home hero, solid once scrolled. The
	   is-scrolled class also tucks the phone CTA bar away, so it runs
	   on every page, not just home. */
	var header = document.querySelector( '.cds-header' );

	if ( header ) {
		var setHeaderState = function () {
			header.classList.toggle( 'is-scrolled', window.scrollY > 40 );
		};

		window.addEventListener( 'scroll', setHeaderState, { passive: true } );
		setHeaderState();
	}

	/* Fade in: reveal cds-fade elements as they enter the viewport. */
	var fades = document.querySelectorAll( '.cds-fade' );

	var showAll = function () {
		fades.forEach( function ( el ) {
			el.classList.add( 'cds-fade--in' );
		} );
	};

	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	if ( reduceMotion || ! ( 'IntersectionObserver' in window ) ) {
		showAll();
		return;
	}

	var observer = new IntersectionObserver(
		function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'cds-fade--in' );
					observer.unobserve( entry.target );
				}
			} );
		},
		{ threshold: 0.12 }
	);

	fades.forEach( function ( el ) {
		observer.observe( el );
	} );
} )();
