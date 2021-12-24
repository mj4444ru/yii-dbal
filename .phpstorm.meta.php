<?php

namespace PHPSTORM_META {

    registerArgumentsSet(
        'doctrineDbalParameterType',
        \Doctrine\DBAL\ParameterType::NULL,
        \Doctrine\DBAL\ParameterType::INTEGER,
        \Doctrine\DBAL\ParameterType::STRING,
        \Doctrine\DBAL\ParameterType::LARGE_OBJECT,
        \Doctrine\DBAL\ParameterType::BOOLEAN,
        \Doctrine\DBAL\ParameterType::BINARY,
        \Doctrine\DBAL\ParameterType::ASCII
    );

    registerArgumentsSet(
        'yiiDbalExpressionBuilderOperator',
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::EQ,
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::NEQ,
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::LT,
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::LTE,
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::GT,
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::GTE
    );

    expectedArguments(
        \Yii\ActiveRecord\DBAL\Parameter::__construct(),
        1,
        argumentsSet('doctrineDbalParameterType')
    );

    expectedArguments(
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::createParameter(),
        1,
        argumentsSet('doctrineDbalParameterType')
    );

    expectedArguments(
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::comparison(),
        1,
        argumentsSet('yiiDbalExpressionBuilderOperator')
    );

    expectedArguments(
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::comparison(),
        3,
        argumentsSet('doctrineDbalParameterType')
    );

    expectedArguments(
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::comparisonColumns(),
        1,
        argumentsSet('yiiDbalExpressionBuilderOperator')
    );

    expectedArguments(
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::comparisonSqlFragment(),
        1,
        argumentsSet('yiiDbalExpressionBuilderOperator')
    );

    expectedArguments(
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::comparisonSqlFragment(),
        3,
        argumentsSet('doctrineDbalParameterType')
    );

    expectedArguments(
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::eq(),
        2,
        argumentsSet('doctrineDbalParameterType')
    );

    expectedArguments(
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::neq(),
        2,
        argumentsSet('doctrineDbalParameterType')
    );

    expectedArguments(
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::lt(),
        2,
        argumentsSet('doctrineDbalParameterType')
    );

    expectedArguments(
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::lte(),
        2,
        argumentsSet('doctrineDbalParameterType')
    );

    expectedArguments(
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::gt(),
        2,
        argumentsSet('doctrineDbalParameterType')
    );

    expectedArguments(
        \Yii\ActiveRecord\DBAL\Expressions\ExpressionBuilder::gte(),
        2,
        argumentsSet('doctrineDbalParameterType')
    );
}
