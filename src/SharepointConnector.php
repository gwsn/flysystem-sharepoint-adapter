<?php
namespace GWSN\FlysystemSharepoint;

use GWSN\Microsoft\Authentication\AuthenticationService;
use GWSN\Microsoft\Drive\DriveService;
use GWSN\Microsoft\Drive\FileService;
use GWSN\Microsoft\Drive\FolderService;
use GWSN\Microsoft\Sharepoint\SharepointService;

class SharepointConnector
{
    private string $accessToken;

    private DriveService $drive;
    private FileService $file;
    private FolderService $folder;

    public function __construct(
        string $tenantId,
        string $clientId,
        string $clientSecret,
        string $sharepointSite
    )
    {
        $authService = new AuthenticationService();
        $accessToken = $authService->getAccessToken($tenantId, $clientId, $clientSecret);
        $this->setAccessToken($accessToken);

        // Get siteId by site name
        $spSite = new SharepointService($accessToken);
        $sharepointHostname = $spSite->requestSharepointHostname();
        $siteId = $spSite->requestSiteIdBySiteName($sharepointHostname, $sharepointSite);

        // Get driveId by site
        $this->drive = new DriveService($accessToken);
        $driveId = $this->drive->requestDriveId($siteId);
        $this->drive->setDriveId($driveId);

        $this->folder = new FolderService($accessToken, $driveId);
        $this->file = new FileService($accessToken, $driveId);
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * @param string $accessToken
     * @return SharepointConnector
     */
    public function setAccessToken(string $accessToken): SharepointConnector
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @return DriveService
     */
    public function getDrive(): DriveService
    {
        return $this->drive;
    }

    /**
     * @param DriveService $drive
     * @return SharepointConnector
     */
    public function setDrive(DriveService $drive): SharepointConnector
    {
        $this->drive = $drive;
        return $this;
    }

    /**
     * @return FileService
     */
    public function getFile(): FileService
    {
        return $this->file;
    }

    /**
     * @param FileService $file
     * @return SharepointConnector
     */
    public function setFile(FileService $file): SharepointConnector
    {
        $this->file = $file;
        return $this;
    }

    /**
     * @return FolderService
     */
    public function getFolder(): FolderService
    {
        return $this->folder;
    }

    /**
     * @param FolderService $folder
     * @return SharepointConnector
     */
    public function setFolder(FolderService $folder): SharepointConnector
    {
        $this->folder = $folder;
        return $this;
    }

}
