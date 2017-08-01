<?php

/*
 * This file is part of the overtrue/wechat.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace EasyWeChat\OfficialAccount\Broadcasting;

use EasyWeChat\Kernel\Exceptions\InvalidArgumentException;
use EasyWeChat\Kernel\Exceptions\RuntimeException;
use EasyWeChat\Kernel\Messages\Message;

/**
 * Class Messenger.
 *
 * @author overtrue <i@overtrue.me>
 */
class Messenger
{
    /**
     * Messages target user or group.
     *
     * @var mixed
     */
    protected $to;

    /**
     * Messages.
     *
     * @var Message
     */
    protected $message;

    /**
     * Set message.
     *
     * @param array|\EasyWeChat\Kernel\Messages\Message $message
     *
     * @return \EasyWeChat\OfficialAccount\Broadcasting\Messenger
     */
    public function message(Message $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Set target user or group.
     *
     * @param mixed $to
     *
     * @return Messenger
     */
    public function to($to)
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Build message.
     *
     * @return bool
     *
     * @throws RuntimeException
     */
    public function build()
    {
        if (empty($this->msgType)) {
            throw new RuntimeException('message type not exist.');
        }

        if (empty($this->message)) {
            throw new RuntimeException('No message content to send.');
        }

        $content = (new MessageTransformer($this->message))->transform();

        $group = isset($this->to) ? $this->to : null;

        $message = array_merge($this->buildGroup($group), $content);

        return $message;
    }

    /**
     * @return array
     */
    public function previewByOpenId()
    {
        return $this->buildPreview(Client::PREVIEW_BY_OPENID);
    }

    /**
     * @return array
     */
    public function previewByName()
    {
        return $this->buildPreview(Client::PREVIEW_BY_NAME);
    }

    /**
     * Build preview message.
     *
     * @param string $by
     *
     * @return array
     *
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    protected function buildPreview($by)
    {
        if (empty($this->message)) {
            throw new RuntimeException('No message content to send.');
        }

        if (empty($this->to)) {
            throw new RuntimeException('No to.');
        }

        $content = (new MessageTransformer($this->message))->transform();

        $message = array_merge($this->buildTo($this->to, $by), $content);

        return $message;
    }

    /**
     * Build group.
     *
     * @param mixed $group
     *
     * @return array
     */
    protected function buildGroup($group)
    {
        if (is_null($group)) {
            $group = [
                'filter' => [
                    'is_to_all' => true,
                ],
            ];
        } elseif (is_array($group)) {
            $group = [
                'touser' => $group,
            ];
        } else {
            $group = [
                'filter' => [
                    'is_to_all' => false,
                    'group_id' => $group,
                ],
            ];
        }

        return $group;
    }

    /**
     * Build to.
     *
     * @param string $to
     * @param string $by
     *
     * @return array
     */
    protected function buildTo($to, $by)
    {
        return [
            $by => $to,
        ];
    }

    /**
     * Return property.
     *
     * @param string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}
