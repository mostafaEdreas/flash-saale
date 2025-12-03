<?php
namespace App\Traits;

use App\Models\Idempotency;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait IdempotencyTrait
{
    private function checkIdempotency(Request $request): ?Idempotency
    {
        $key = $request->input('idempotency');

        if (! $key) {
            throw new HttpException(422, 'The idempotency field is required.');
        }

        // Return the idempotency record if it exists
        return Idempotency::where('key', $key)->first();
    }

    private function guardIdempotency(Request $request): void
    {
        if ($this->checkIdempotency($request)) {
            throw new HttpException(400, 'Duplicate request detected.');
        }
    }
    
    private function createIdempotency(Request $request): Idempotency
    {
       
        return Idempotency::create([
            'key' => $request->input('idempotency'),
        ]);
    }

}
