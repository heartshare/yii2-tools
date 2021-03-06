<?php
/**
 * FileDeleteAction class file.
 * @copyright (c) 2015, Pavel Bariev
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

namespace bariew\yii2Tools\actions;
use yii\base\Action;
use Yii;

/**
 * See README
 *
 * @author Pavel Bariev <bariew@yandex.ru>
 */
class FileDeleteAction extends Action
{
    
    /**
     * Deletes file and thumbnails.
     * @param integer $id owner Item model id.
     * @param string $name file name.
     * @return \yii\web\Response
     */
    public function run($id, $name)
    {
        /** @var \bariew\postModule\models\Item $model */
        $model = $this->controller->findModel($id);
        if ($model->deleteFile($name)) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'File successfully deleted'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'File delete error'));
        }
        return $this->controller->redirect(Yii::$app->request->referrer);
    }
}
