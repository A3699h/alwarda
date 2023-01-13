<?php

namespace App\Service;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ApiFormError
 */
class ApiFormError
{
    const DEFAULT_ERROR_LEVEL = 0;

    /**
     * @param FormInterface $form
     * @param int           $level
     *
     * @return array
     */
    public function getFormErrorsAsFormattedArray(FormInterface $form, $level = self::DEFAULT_ERROR_LEVEL)
    {
        $errors = [];

        foreach ($form->getErrors() as $error) {
            if ($level == self::DEFAULT_ERROR_LEVEL) {
                $errors['global'][] = $error->getMessage();
            } else {
                $errors[] = $error->getMessage();
            }
        }

        $fields = $form->all();
        foreach ($fields as $key => $child) {
            if ($error = $this->getFormErrorsAsFormattedArray($child, $level + 4)) {
                $errors[$key] = $error;
            }
        }

        return $errors;
    }

    public function jsonResponseFormError(FormInterface $form)
    {
        $data = $this->getFormErrorsAsFormattedArray($form);

        return new JsonResponse($data, 422);
    }
}
