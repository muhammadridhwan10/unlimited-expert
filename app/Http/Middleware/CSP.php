<?php
namespace App\Http\Middleware;

use Closure;

class ContentSecurityPolicy
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('Content-Security-Policy', "
            default-src 'self'; 
            script-src 'self' https://cdn.jsdelivr.net/npm/chart.js https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css https://cdn.jsdelivr.net/momentjs/latest/moment.min.js https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js https://cdn.jsdelivr.net/npm/bootstrap-daterangepicker@3.1.0/daterangepicker.min.js https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js; 
            style-src 'self' https://cdn1.example.com https://cdn2.example.com https://cdn3.example.com; 
            img-src 'self' https://cdn.dribbble.com/users/285475/screenshots/2083086/dribbble_1.gif data: blob:; 
        ");


        return $response;
    }
}
