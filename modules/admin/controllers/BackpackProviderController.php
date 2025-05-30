<?php

require_once dirname(__DIR__) . '/repositories/BackpackProviderRepository.php';

/**
 * BackpackProvider Controller
 * 
 * Handles HTTP requests and business logic for backpack providers
 */
class BackpackProviderController
{
    private BackpackProviderRepository $repository;

    public function __construct(?BackpackProviderRepository $repository = null)
    {
        $this->repository = $repository ?? new BackpackProviderRepository();
    }

    public function index()
    {
        global $action_bar;
        $providers = $this->repository->findAll();
        $action_bar = action_bar([
            [
                'title' => trans('langNewBackpackProvider'),
                'url' => $_SERVER['SCRIPT_NAME'] . '?action=create',
                'icon' => 'fa-plus-circle',
                'level' => 'primary-label',
                'button-class' => 'btn-success'
            ]
        ]);

        return view('admin.other.extapps.openbadges_index', [
            'providers' => $providers
        ]);
    }

    public function create()
    {
        global $action_bar;
        
        $action_bar = action_bar([
            [
                'title' => trans('langBack'),
                'url' => $_SERVER['SCRIPT_NAME'],
                'icon' => 'fa-reply',
                'level' => 'primary'
            ]
        ]);

        return view('admin.other.extapps.openbadges_form', [
            'provider' => null,
            'action' => 'create',
            'method' => 'POST',
            'submitLabel' => trans('langAdd'),
            'formAction' => $_SERVER['SCRIPT_NAME'] . '?action=store'
        ]);
    }

    public function store()
    {
        try {
            $this->validateCsrfToken();
            
            $providerData = $this->getProviderDataFromRequest();
            $provider = BackpackProvider::create(
                $providerData['name'],
                $providerData['api_url'],
                $providerData['ob_version']
            );

            $savedProvider = $this->repository->save($provider);
            
            if ($savedProvider) {
                $this->flashAndRedirect(
                    trans('langBackpackProviderAdded'), 
                    'alert-success', 
                    $_SERVER['SCRIPT_NAME']
                );
            } else {
                throw new RuntimeException('Failed to save provider');
            }
        } catch (InvalidArgumentException $e) {
            $this->flashAndRedirect(
                $e->getMessage(), 
                'alert-danger', 
                $_SERVER['SCRIPT_NAME'] . '?action=create'
            );
        } catch (Exception $e) {
            error_log('Failed to create backpack provider: ' . $e->getMessage());
            $this->flashAndRedirect(
                trans('langBackpackProviderAddFailed'), 
                'alert-danger', 
                $_SERVER['SCRIPT_NAME'] . '?action=create'
            );
        }
    }

    public function edit(string $id)
    {
        global $action_bar;
        
        $directId = getDirectReference($id);
        $provider = $this->repository->findById($directId);

        if (!$provider) {
            $this->flashAndRedirect(
                trans('langProviderNotFound'), 
                'alert-danger', 
                $_SERVER['SCRIPT_NAME']
            );
        }

        $action_bar = action_bar([
            [
                'title' => trans('langBack'),
                'url' => $_SERVER['SCRIPT_NAME'],
                'icon' => 'fa-reply',
                'level' => 'primary'
            ]
        ]);

        return view('admin.other.extapps.openbadges_form', [
            'provider' => $provider,
            'action' => 'edit',
            'method' => 'POST',
            'submitLabel' => trans('langEditChange'),
            'formAction' => $_SERVER['SCRIPT_NAME'] . '?action=update&id=' . $id
        ]);
    }

    public function update(string $id)
    {
        try {
            $this->validateCsrfToken();
            
            $directId = getDirectReference($id);
            $provider = $this->repository->findById($directId);
            
            if (!$provider) {
                throw new InvalidArgumentException(trans('langProviderNotFound'));
            }

            $providerData = $this->getProviderDataFromRequest();
            $updatedProvider = $provider->update(
                $providerData['name'],
                $providerData['api_url'],
                $providerData['ob_version'],
                isset($_POST['active']) && $_POST['active'] == '1'
            );

            $savedProvider = $this->repository->update($updatedProvider);
            
            if ($savedProvider) {
                $this->flashAndRedirect(
                    trans('langBackpackProviderUpdated'), 
                    'alert-success', 
                    $_SERVER['SCRIPT_NAME']
                );
            } else {
                throw new RuntimeException('Failed to update provider');
            }
        } catch (InvalidArgumentException $e) {
            $this->flashAndRedirect(
                $e->getMessage(), 
                'alert-danger', 
                $_SERVER['SCRIPT_NAME'] . '?action=edit&id=' . $id
            );
        } catch (Exception $e) {
            error_log('Failed to update backpack provider: ' . $e->getMessage());
            $this->flashAndRedirect(
                trans('langBackpackProviderUpdateFailed'), 
                'alert-danger', 
                $_SERVER['SCRIPT_NAME'] . '?action=edit&id=' . $id
            );
        }
    }

    public function delete(string $id)
    {
        error_log("Attempting to delete provider with ID: " . $id);

        try {
            $directId = getDirectReference($id);
            $deleted = $this->repository->delete($directId);
            
            if ($deleted) {
                $this->flashAndRedirect(
                    trans('langBackpackProviderDeleted'), 
                    'alert-success', 
                    $_SERVER['SCRIPT_NAME']
                );
            } else {
                throw new RuntimeException('Failed to delete provider');
            }
        } catch (Exception $e) {
            error_log('Failed to delete backpack provider: ' . $e->getMessage());
            $this->flashAndRedirect(
                trans('langBackpackProviderDeleteFailed'), 
                'alert-danger', 
                $_SERVER['SCRIPT_NAME']
            );
        }
    }

    private function getProviderDataFromRequest(): array
    {
        $requiredFields = ['provider_name', 'api_url', 'version'];
        
        return array_reduce($requiredFields, function($carry, $field) {
            $value = $_POST[$field] ?? '';
            $carry[str_replace('provider_', '', $field)] = trim($value);
            if ($field === 'version') {
                $carry['ob_version'] = $carry['version'];
                unset($carry['version']);
            }
            return $carry;
        }, []);
    }

    private function validateCsrfToken(): void
    {
        if (!isset($_POST['token']) || !validate_csrf_token($_POST['token'])) {
            csrf_token_error();
        }
    }

    /**
     * Flash message and redirect
     */
    private function flashAndRedirect(string $message, string $class, string $redirect): never
    {
        Session::flash('message', $message);
        Session::flash('alert-class', $class);
        redirect_to_home_page($redirect);
        exit;
    }

    public function handleRequest()
    {
        $action = $_GET['action'] ?? 'index';
        $method = $_SERVER['REQUEST_METHOD'];

        return match(true) {
            $action === 'index' => $this->index(),
            $action === 'create' && $method === 'GET' => $this->create(),
            $action === 'store' && $method === 'POST' => $this->store(),
            $action === 'edit' && $method === 'GET' && isset($_GET['id']) => $this->edit($_GET['id']),
            $action === 'update' && $method === 'POST' && isset($_GET['id']) => $this->update($_GET['id']),
            $action === 'delete' && $method === 'POST' && isset($_GET['id']) => $this->delete($_GET['id']),
            default => $this->index()
        };
    }
} 