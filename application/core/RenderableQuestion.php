<?php

/**
 * This interface represents a question that can be rendered in HTML
 * given the information array and a context (survey, state, etc..)
 *
 * The idea is to find classes that implement this interface and as each question
 * is iterated during survey render the appropriate class will be called and passed the
 * necessary information to render the question. The distinction will use 
 * the `getCode()` method to match implementations to questions.
 */
interface RenderableQuestion {
    /**
     * This function determines the Code that corresponds to the question type.
     */
    public function getCode();

    /**
     * Initializes the question with the information array.
     */
    public function init($ia, $context);

    /**
     * Obtains the questions meta attributes.
     */
    public function getAttributes();

    /**
     * Render the question; Obtain the HTML string.
     */
    public function render();
}
