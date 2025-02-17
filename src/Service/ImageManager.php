<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Image;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageManager
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
        private readonly Filesystem $filesystem,
        private readonly S3Client $s3Client,
        private readonly string $s3CacheBucket
    ) {
    }

    /**
     * Create file on abstracted filesystem (currently S3).
     *
     * @throws FilesystemException
     */
    public function upload(UploadedFile $file, string $coasterSlug = null): string
    {
        $filename = $this->generateFilename($file, $coasterSlug);

        $this->filesystem->write(
            $filename,
            $file->getContent()
        );

        return $filename;
    }

    /**
     * Remove file from abstracted filesystem (currently S3).
     *
     * @throws FilesystemException
     */
    public function remove(string $filename): void
    {
        $this->filesystem->delete($filename);
    }

    /** Remove file from S3 Cache Bucket. */
    public function removeCache(Image $image): void
    {
        $this->s3Client->deleteObjects([
            'Bucket' => $this->s3CacheBucket,
            'Delete' => [
                'Objects' => [
                    ['Key' => '1440x1440/'.$image->getFilename()],
                    ['Key' => '600x336/'.$image->getFilename()],
                    ['Key' => '280x210/'.$image->getFilename()],
                    ['Key' => '96x96/'.$image->getFilename()],
                ],
            ],
        ]);
    }

    /**
     * Update main image property of all coasters.
     *
     * @todo faire mieux :)
     */
    public function setMainImages(): void
    {
        $conn = $this->em->getConnection();

        $sql = 'update coaster c
            left join (
	            select sub.id, sub.coaster_id from (
		            select * from image
		            where enabled = 1
		            order by like_counter desc, updated_at desc
		            limit 18446744073709551615) as sub
	            group by coaster_id
            ) as i on i.coaster_id = c.id
            set c.main_image_id = i.id;';

        try {
            $stmt = $conn->prepare($sql);
            $stmt->executeStatement();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Update like counters for all images.
     *
     * @todo faire mieux :)
     */
    public function updateLikeCounters(): void
    {
        $conn = $this->em->getConnection();

        $sql = 'update image i1
            join (
                select i.id, count(li.image_id) as nb from image i
                left join liked_image li on li.image_id = i.id
                group by i.id
            ) as i2
            on i2.id = i1.id
            set i1.like_counter = i2.nb;';

        try {
            $stmt = $conn->prepare($sql);
            $stmt->executeStatement();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /** Generates a filename like fury-325-carowinds-64429c62b6b23.jpg. */
    private function generateFilename(UploadedFile $file, string $coasterSlug = null): string
    {
        return sprintf('%s-%s.%s', $coasterSlug, uniqid(), $file->guessExtension());
    }
}
