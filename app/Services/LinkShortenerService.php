<?php

namespace App\Services;

use App\Exceptions\LinkGenerationException;
use App\Enums\LinkGenerationError;
use App\Models\Link;
use App\Models\User;
use Random\RandomException;

final readonly class LinkShortenerService
{
    /**
     * @throws RandomException
     * @throws LinkGenerationException
     */
    public function createShortLink(User $user, string $originalUrl): Link
    {
        $code = $this->generateUniqueCode();

        return Link::create([
            'user_id' => $user->id,
            'url' => $originalUrl,
            'code' => $code,
        ]);
    }


    /**
     * @throws RandomException
     * @throws LinkGenerationException
     */
    private function generateUniqueCode(): string
    {
        $startTime = microtime(true);
        $timeLimit = 1.0;
        $batchSize = 100;
        $attempt = 0;

        $minLength = 6;
        $maxLength = 32;
        while (true) {
            $attempt++;
            $duration = (int)round((microtime(true) - $startTime) * 1000);
            if ($minLength > $maxLength) {
                throw new LinkGenerationException(
                    LinkGenerationError::DATABASE_FULL,
                    [
                        'total_attempts' => $attempt,
                        'time_spent' => $duration . ' ms',
                        'length' => $minLength,
                        'batch_size' => $batchSize
                    ]);
            }
            if ($duration > $timeLimit) {
                throw new LinkGenerationException(LinkGenerationError::TIMEOUT,
                    [
                        'total_attempts' => $attempt,
                        'time_spent' => $duration . ' ms',
                        'batch_size' => $batchSize
                    ]);
            }
            $codes = [];
            for ($i = 0; $i < $batchSize; $i++) {
                $codes[] = $this->generateRandomCode($minLength);
            }

            $codes = array_unique($codes);

            $existingCodes = Link::whereIn('code', $codes)->pluck('code')->toArray();
            $availableCodes = array_diff($codes, $existingCodes);

            if (!empty($availableCodes)) {
                return reset($availableCodes);
            }
            $minLength++;
        }
    }

    /**
     * Генерация случайного кода
     * @throws RandomException
     */
    private function generateRandomCode(int $length): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $bytes = random_bytes($length);
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[ord($bytes[$i]) % 62];
        }
        return $code;
    }
}
