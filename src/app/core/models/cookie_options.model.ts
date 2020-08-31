export class cookie_options {
    constructor(
        public history: boolean = true,         // CSS history knocking or not .. can be network intensive
        public java: boolean = true,            // Java applet on/off... may prompt users for permission to run.
        public tests: Number = 10,
        public silverlight: boolean = true,     // you might want to turn it off https://github.com/samyk/evercookie/issues/45
        public lso: boolean = true, 	        // Turn local storage cookies on and off.
        public baseurl: string = '',            // base url (eg: www.sitename.com/demo use /demo)
        public asseturi: string = '/assets',    // asset path (eg: www.sitename.com/assets use /assets)
        public phpuri: string = '/backend/app', // php path/route (eg: www.sitename.com/php use /php)
        public domain: string = '.' + window.location.host.replace(/:\d+/, ''), // as a string, domain for cookie, as a function, accept window object and return domain string
        public swfFileName: string = '/evercookie.swf',
        public xapFileName: string = '/evercookie.xap', 
        public jnlpFileName: string = '/evercookie.jnlp', 
        public pngCookieName: string = undefined, //'rcv.vote.png', 
        public pngPath: string = '/evercookie_png.php',
        public etagCookieName: string = undefined, //'rcv.vote.etag', 
        public etagPath: string = '/evercookie_etag.php',
        public cacheCookieName: string = undefined, //'rcv.vote.cache',
        public cachePath: string = '/evercookie_cache.php',
        public hsts: boolean = false, 	        // Turn hsts cookies on and off.
        public hsts_domains: string[] = [''],   // The domains used for the hsts cookie. 1 Domain = one bit (8 domains => 8 bit => values up to 255)
        public db: boolean = true,  	        // Turn db cookies on and off.
        public idb: boolean = true,  	        // Turn indexed db cookies on and off.
        public jnlp_file_name: string = '',
        public swf_file_name: string = '',
        public xap_file_name: string = ''
    ){}
}