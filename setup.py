"""Installer for memorised
"""

import os
cwd = os.path.dirname(__file__)

try:
    from setuptools import setup, find_packages
except ImportError:
    from ez_setup import use_setuptools
    use_setuptools()
    from setuptools import setup, find_packages

setup(
        name='memorised',
        version='1.0.3',
        author='Wes Mason',
        author_email='wes@1stvamp.org',
        description='memcache memoization decorators and utils for python',
        long_description=open(os.path.join(cwd, 'README.rst')).read(),
        url='http://github.com/1stvamp//memorised/',
        packages=find_packages(exclude=('ez_setup',)),
        install_requires=('python-memcached',),
        license='BSD'
)
