<?php

namespace Ninja\Helper;

use \CUser;
use \Bitrix\Main\UserTable;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\ArgumentNullException;
use \Bitrix\Main\ArgumentOutOfRangeException;


class User {
    /**
     * Возвращает список пользователей. Если выполняется сложная фильтрация выборка ID обязательна!
     *
     * @param array{
     *     FILTER: string[],
     *     SELECT: array{
     *          FIELDS: array,
     *          SELECT: array
     *     },
     *     NAV_PARAMS: array{
     *          nTopCount: int,
     *          nPageSize: int
     *      },
     *      ORDER: array,
     *     AS_ARRAY: string
     * } $params
     * @return array
     */
    public static function getList(array $params = []): array {
        // Определение фильтра
        $filter = [];
        if (!empty($params['FILTER'])) {
            $filter = $params['FILTER'];

            if (is_array($filter['ID'])) {
                $filter['ID'] = implode(' | ', $filter['ID']);
            }
        }

        // Определение полей выборки
        $select = $params['SELECT'] ?? [];

        // Разбираем поля для выборки
        $selectToBd = [];
        foreach ($select as $key => $selectItem) {
            $selectToBd[$key] = (new TypeConvert($selectItem))->getSelect();
        }

        $by    = $params['ORDER'] ?? 'timestamp_x';
        $order = 'desc';

        $userList = CUser::GetList(
            $by, $order,
            $filter,
            $selectToBd
        );

        // Формирование результата
        $result = [];

        while ($user = $userList->Fetch()) {
            if (array_key_exists('AS_ARRAY', $params) === true && $params['AS_ARRAY'] === 'Y') {
                $result[] = $user;
            } else {
                $result[$user['ID']] = $user;
            }
        }

        // Приведем массив к нужным типам данных
        $selectAll = array_merge(
            $select['FIELDS'] ?? [],
            $select['SELECT'] ?? []
        );

        $typeConverter = new TypeConvert($selectAll);

        if ($typeConverter->getTypes()) {
            $result = $typeConverter->convertDataTypes($result);
        }

        return $result;
    }


    /**
     * Возвращает идентификаторы групп пользователя
     *
     * @return array
     */
    public static function getGroupCurrentUser(): array {
        global $USER;

        return $USER->GetUserGroupArray();
    }


    /**
     * Возвращает идентификатор авторизованного пользователя
     *
     * @return int|null
     */
    public static function getAuthorizedId(): ?int {
        global $USER;

        $userId = (int)$USER->GetID();

        return $userId === 0 ? null : $userId;
    }


    /**
     * Возвращает статус авторизации
     *
     * @return bool
     */
    public static function isAuthorize(): bool {
        global $USER;

        return $USER->IsAuthorized();
    }


    /**
     * Авторизует пользователя
     *
     * @param string $login
     * @param string $password
     * @param bool $remember - сохраняем ли авторизацию в куки
     * @param string $capthaSid
     * @param string $captchaValue
     * @return array
     */
    public static function authorize(string $login, string $password, bool $remember = true, string $capthaSid = null, string $captchaValue = null): array {
        $out = [];

        global $USER;

        // Данные капчи передаются в Битриксе в суперглобальном массиве $_REQUEST
        if ($captchaValue !== null && $capthaSid !== null) {
            $_REQUEST['captcha_word'] = $captchaValue;
            $_REQUEST['captcha_sid']  = $capthaSid;
        }

        $result = $USER->Login($login, $password, $remember ? 'Y' : 'N', 'Y');

        if ($result === true) {
            $out['status'] = true;
        } else {
            $out = [
                'status'    => false,
                'errorType' => $result['ERROR_TYPE'],
                'message'   => $result['MESSAGE'],
            ];
        }

        return $out;
    }


    /**
     * Проверяет необходима ли капча для авторизации пользователю
     *
     * @param string $login
     * @return bool
     */
    public static function isCaptchaAuthorization(string $login): bool {
        global $APPLICATION;

        return $APPLICATION->NeedCAPTHAForLogin($login) === true;
    }


    /**
     * Разлогинивает пользователя
     */
    public static function logout(): void {
        global $USER;

        $USER->Logout();
    }


    /**
     * Обновляет данные пользователя
     *
     * @param int $userId
     * @param array $update
     * @return array
     */
    public static function update(int $userId, array $update): array {
        $user = new CUser();

        $is = $user->Update($userId, $update);

        if ($is) {
            $result = [
                'status' => true,
            ];
        } else {
            $result = [
                'status'  => false,
                'message' => $user->LAST_ERROR,
            ];
        }

        return $result;
    }


    /**
     * Проверяет уникальность e-mail
     *
     * @param string $email
     * @return int|null
     */
    public static function findIdByEmail(string $email): ?int {
        $user = UserTable::getRow([
            'filter' => [
                '=EMAIL' => $email,
            ],
            'select' => [
                'ID',
            ],
            'limit'  => 1,
        ]);

        return !empty($user['ID']) ? (int) $user['ID'] : null;
    }


    /**
     * Проверяет уникальность логина
     *
     * @param string $login
     * @return int|null
     */
    public static function findIdByLogin(string $login): ?int {
        $user = UserTable::getRow([
            'filter' => [
                '=LOGIN' => $login,
            ],
            'select' => [
                'ID',
            ],
            'limit'  => 1,
        ]);

        return !empty($user['ID']) ? (int) $user['ID'] : null;
    }


    /**
     * Отправляет запрос восстановления пароля
     *
     * @param string|null $login
     * @param string|null $email
     * @param array{siteId: string, captchaValue: string, captchaSid: string} $additional
     * @return array
     */
    public static function requestChangePassword(?string $login, ?string $email, array $additional = []): array {
        $siteId      = $additional['siteId'] ?? SITE_ID;
        $captchaValue = $additional['captchaValue'] ?? SITE_ID;
        $captchaSid  = $additional['captchaSid'] ?? SITE_ID;

        $status = CUser::SendPassword($login, $email, $siteId, $captchaValue, $captchaSid);

        return [
            'isSuccess' => $status['TYPE'] === 'OK',
            'message'   => $status['MESSAGE'],
        ];
    }


    /**
     * Изменяет пароль пользователя
     *
     * @param string $login
     * @param string $checkword
     * @param string $password
     * @param string $passwordConfirm
     * @param array{siteId: string, captchaValue: string, captchaSid: string} $additional
     * @return array
     */
    public static function changePassword(string $login, string $checkword, string $password, string $passwordConfirm, array $additional = []): array {
        $user = new CUser();

        $siteId      = $additional['siteId'] ?? SITE_ID;
        $captchaValue = $additional['captchaValue'] ?? SITE_ID;
        $captchaSid  = $additional['captchaSid'] ?? SITE_ID;

        $status = $user->ChangePassword($login, $checkword, $password, $passwordConfirm, $siteId, $captchaValue, $captchaSid);

        return [
            'isSuccess' => $status['TYPE'] === 'OK',
            'message'   => $status['MESSAGE'],
        ];
    }


    /**
     * Проверяет необходимость использования капчи для восстановления пароля
     *
     * @return bool
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public static function isCaptchaChangePassword(): bool {
        return Option::get('main', 'captcha_restoring_password', 'N') === 'Y';
    }
}
