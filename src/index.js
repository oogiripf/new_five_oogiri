import '@babel/polyfill';
import './sass/style.scss';

import Scroller from './js/scroller';
import { Watch }  from './js/Watch';
import './js/swiper.js';
import './js/fadein.js';
import './js/svg.js';
import './js/matrix.js';

const scroller = new Scroller( window, document, [ 'second-sec', 'third-sec', 'third-triangle', 'sec-htu-child-title', 'sec-htu-img', 'fifth-sec-title', 'fifth-sec', ] );
let watch = new Watch( window, 'third-sec', 'wrapper' );

const fps = 30;
let scrollFlg = false;

const ua = window.navigator.userAgent.toLowerCase();
const isIE = ua.indexOf('msie') != -1 || ua.indexOf('trident') != -1 ? true : false;